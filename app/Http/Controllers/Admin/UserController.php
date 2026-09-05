<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserProfile;
use App\Support\AdminPagination;
use App\Support\AdminShell;
use App\Support\Format;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /** @var list<string> */
    private const VERIFICATION_STATUSES = ['verified', 'unverified'];

    public function index(Request $request): Response
    {
        $filters = $this->filters($request);
        $query = User::query()
            ->where('role', UserRole::User->value)
            ->with('profile')
            ->withCount(['addresses', 'orders']);

        if ($filters['verification'] === 'verified') {
            $query->whereNotNull('email_verified_at');
        } elseif ($filters['verification'] === 'unverified') {
            $query->whereNull('email_verified_at');
        }

        if ($filters['search'] !== '') {
            $query->where(function ($searchQuery) use ($filters) {
                $searchQuery
                    ->where('username', 'like', '%'.$filters['search'].'%')
                    ->orWhere('email', 'like', '%'.$filters['search'].'%');
            });
        }

        $users = $query->latest('id')->paginate(15)->appends(array_filter([
            'q' => $filters['search'],
            'verification' => $filters['verification'],
        ], fn (string $value): bool => $value !== ''));

        return Inertia::render('Admin/Users/Index', [
            'copy' => [
                ...AdminShell::copy(),
                ...$this->indexCopy(),
            ],
            'filters' => $filters,
            'options' => [
                'verificationStatuses' => [
                    ['value' => 'verified', 'label' => __('Terverifikasi')],
                    ['value' => 'unverified', 'label' => __('Belum terverifikasi')],
                ],
            ],
            'routes' => AdminShell::routes(),
            'users' => [
                'data' => $users->getCollection()
                    ->map(fn (User $user): array => $this->serializeUser($user))
                    ->values()
                    ->all(),
                'pagination' => AdminPagination::serialize($users, __('Menampilkan :from–:to dari :total pengguna', [
                    'from' => Format::number($users->firstItem() ?? 0),
                    'to' => Format::number($users->lastItem() ?? 0),
                    'total' => Format::number($users->total()),
                ])),
            ],
        ]);
    }

    public function show(User $user): Response
    {
        abort_unless($user->role === UserRole::User, 404);

        $user->load([
            'profile',
            'addresses' => fn ($query) => $query
                ->orderByDesc('is_default')
                ->orderBy('id'),
        ])->loadCount('orders');

        return Inertia::render('Admin/Users/Show', [
            'account' => $this->serializeUserDetail($user),
            'copy' => [
                ...AdminShell::copy(),
                ...$this->detailCopy(),
            ],
            'routes' => [
                ...AdminShell::routes(),
                'ordersForUser' => route('admin.orders.index', ['q' => $user->email], absolute: false),
            ],
        ]);
    }

    /** @return array{search: string, verification: string} */
    private function filters(Request $request): array
    {
        $verification = $request->string('verification')->trim()->toString();

        return [
            'search' => mb_substr($request->string('q')->trim()->toString(), 0, 255),
            'verification' => in_array($verification, self::VERIFICATION_STATUSES, true)
                ? $verification
                : '',
        ];
    }

    /** @return array<string, mixed> */
    private function serializeUser(User $user): array
    {
        $profile = $user->getRelation('profile');
        $createdAt = $user->getAttribute('created_at');
        $verifiedAt = $user->getAttribute('email_verified_at');

        return [
            'addressCount' => (int) $user->getAttribute('addresses_count'),
            'email' => (string) $user->email,
            'fullName' => $profile instanceof UserProfile
                ? $this->nullableString($profile->full_name)
                : null,
            'id' => (int) $user->getKey(),
            'joinedAt' => Format::date($createdAt instanceof DateTimeInterface || is_string($createdAt) ? $createdAt : null),
            'orderCount' => (int) $user->getAttribute('orders_count'),
            'username' => (string) $user->username,
            'verification' => [
                'label' => $verifiedAt === null ? __('Belum terverifikasi') : __('Terverifikasi'),
                'verified' => $verifiedAt !== null,
            ],
            'url' => route('admin.users.show', $user, absolute: false),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeUserDetail(User $user): array
    {
        $profile = $user->getRelation('profile');
        /** @var Collection<int, UserAddress> $addresses */
        $addresses = $user->getRelation('addresses');
        $createdAt = $user->getAttribute('created_at');
        $lastSeen = $user->getAttribute('last_seen');
        $verifiedAt = $user->getAttribute('email_verified_at');
        $orderCount = $user->getAttribute('orders_count');
        $preferredLocale = $profile instanceof UserProfile
            ? $this->nullableString($profile->preferred_locale)
            : null;
        $spent = $user->orders()
            ->whereIn('status', OrderStatus::revenueValues())
            ->sum('total_price');

        return [
            'addresses' => $addresses
                ->map(fn (UserAddress $address): array => $this->serializeAddress($address))
                ->values()
                ->all(),
            'email' => (string) $user->email,
            'id' => (int) $user->getKey(),
            'joinedAt' => Format::dateTime($createdAt instanceof DateTimeInterface || is_string($createdAt) ? $createdAt : null),
            'lastSeenAt' => Format::dateTime($lastSeen instanceof DateTimeInterface || is_string($lastSeen) ? $lastSeen : null),
            'orderSummary' => [
                'count' => Format::number(is_numeric($orderCount) ? $orderCount : 0),
                'spent' => Format::idr($spent),
            ],
            'profile' => [
                'fullName' => $profile instanceof UserProfile
                    ? $this->nullableString($profile->full_name)
                    : null,
                'phone' => $profile instanceof UserProfile
                    ? $this->nullableString($profile->phone)
                    : null,
                'preferredLocale' => $preferredLocale === null ? null : [
                    'label' => match ($preferredLocale) {
                        'id' => __('Bahasa Indonesia'),
                        'en' => __('Bahasa Inggris'),
                        default => strtoupper($preferredLocale),
                    },
                    'value' => $preferredLocale,
                ],
            ],
            'username' => (string) $user->username,
            'verification' => [
                'label' => $verifiedAt === null ? __('Belum terverifikasi') : __('Terverifikasi'),
                'verified' => $verifiedAt !== null,
                'verifiedAt' => Format::dateTime($verifiedAt instanceof DateTimeInterface || is_string($verifiedAt) ? $verifiedAt : null),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function serializeAddress(UserAddress $address): array
    {
        return [
            'addressLine1' => (string) $address->address_line_1,
            'addressLine2' => $this->nullableString($address->address_line_2),
            'city' => (string) $address->city,
            'country' => $address->country_code === 'ID'
                ? __('Indonesia')
                : (string) $address->country_code,
            'countryCode' => (string) $address->country_code,
            'id' => (int) $address->getKey(),
            'isDefault' => (bool) $address->is_default,
            'label' => (string) $address->label,
            'postalCode' => (string) $address->postal_code,
            'province' => (string) $address->province,
            'recipientName' => (string) $address->recipient_name,
            'recipientPhone' => (string) $address->recipient_phone,
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /** @return array<string, string> */
    private function indexCopy(): array
    {
        return [
            'actions' => __('Aksi'),
            'addresses' => __('Alamat'),
            'all' => __('Semua'),
            'applyFilters' => __('Terapkan Filter'),
            'description' => __('Tinjau akun pelanggan, kelengkapan profil, dan aktivitas pesanannya.'),
            'detail' => __('Detail'),
            'emptyDescription' => __('Ubah kata pencarian atau filter untuk menemukan pengguna lain.'),
            'emptyTitle' => __('Tidak ada pengguna yang sesuai.'),
            'eyebrow' => __('Manajemen akun'),
            'filtersDescription' => __('Pencarian dibatasi pada username dan email karena data pribadi lainnya disimpan terenkripsi.'),
            'filtersTitle' => __('Cari pengguna'),
            'fullName' => __('Nama lengkap'),
            'joined' => __('Bergabung'),
            'next' => __('Berikutnya'),
            'ordersCount' => __('Pesanan'),
            'previous' => __('Sebelumnya'),
            'reset' => __('Reset'),
            'resultsDescription' => __('Hanya akun pelanggan yang ditampilkan; akun administrator tidak termasuk.'),
            'resultsTitle' => __('Daftar pengguna'),
            'search' => __('Cari'),
            'searchPlaceholder' => __('Username atau email'),
            'title' => __('Pengguna'),
            'username' => __('Username'),
            'verification' => __('Verifikasi email'),
        ];
    }

    /** @return array<string, string> */
    private function detailCopy(): array
    {
        return [
            'accountDescription' => __('Identitas akun dan status verifikasi pengguna.'),
            'accountTitle' => __('Informasi akun'),
            'addressesDescription' => __('Alamat pengiriman tersimpan milik pengguna ini.'),
            'addressesTitle' => __('Alamat pengiriman'),
            'back' => __('Kembali ke daftar pengguna'),
            'cityProvince' => __('Kota dan provinsi'),
            'country' => __('Negara'),
            'defaultAddress' => __('Alamat utama'),
            'email' => __('Email'),
            'eyebrow' => __('Detail pengguna'),
            'fullName' => __('Nama lengkap'),
            'joined' => __('Bergabung'),
            'lastSeen' => __('Terakhir aktif'),
            'noAddresses' => __('Pengguna belum menyimpan alamat pengiriman.'),
            'notProvided' => __('Belum diisi'),
            'orderCount' => __('Jumlah pesanan'),
            'ordersDescription' => __('Ringkasan aktivitas pesanan yang terkait dengan akun ini.'),
            'ordersTitle' => __('Aktivitas pesanan'),
            'phone' => __('Nomor telepon'),
            'preferredLocale' => __('Bahasa pilihan'),
            'privacyNotice' => __('Data pribadi ini bersifat sensitif. Gunakan hanya untuk dukungan akun dan pemenuhan pesanan.'),
            'profileDescription' => __('Data pribadi yang disimpan terenkripsi oleh aplikasi.'),
            'profileTitle' => __('Data pribadi'),
            'recipient' => __('Penerima'),
            'spent' => __('Total belanja'),
            'title' => __('Detail pengguna'),
            'username' => __('Username'),
            'verification' => __('Verifikasi email'),
            'verifiedAt' => __('Diverifikasi pada'),
            'viewOrders' => __('Lihat pesanan pengguna'),
        ];
    }
}
