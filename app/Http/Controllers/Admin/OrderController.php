<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Souvenir;
use App\Models\User;
use App\Support\AdminShell;
use App\Support\Format;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /** @var list<string> */
    private const ORDER_STATUSES = ['pending', 'processing', 'completed', 'cancelled'];

    /** @var list<string> */
    private const PAYMENT_STATUSES = ['unpaid', 'pending', 'paid', 'failed', 'expired', 'refunded'];

    public function index(Request $request): Response
    {
        $filters = $this->filters($request);
        $query = Order::query()->with(['user', 'payment']);

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if ($filters['paymentStatus'] === 'unpaid') {
            $query->whereDoesntHave('payment');
        } elseif ($filters['paymentStatus'] !== '') {
            $query->whereHas('payment', function ($paymentQuery) use ($filters) {
                $paymentQuery->where('status', $filters['paymentStatus']);
            });
        }

        if ($filters['dateFrom'] !== '') {
            $query->whereDate('created_at', '>=', $filters['dateFrom']);
        }

        if ($filters['dateTo'] !== '') {
            $query->whereDate('created_at', '<=', $filters['dateTo']);
        }

        if ($filters['search'] !== '') {
            $query->where(function ($searchQuery) use ($filters) {
                if (is_numeric($filters['search'])) {
                    $searchQuery->where('id', (int) $filters['search']);
                }

                $searchQuery->orWhereHas('user', function ($userQuery) use ($filters) {
                    $userQuery->where('email', 'like', '%'.$filters['search'].'%')
                        ->orWhere('username', 'like', '%'.$filters['search'].'%');
                });
            });
        }

        $orders = $query->latest()->paginate(15)->appends(array_filter([
            'q' => $filters['search'],
            'status' => $filters['status'],
            'payment_status' => $filters['paymentStatus'],
            'date_from' => $filters['dateFrom'],
            'date_to' => $filters['dateTo'],
        ], fn (string $value): bool => $value !== ''));

        return Inertia::render('Admin/Orders/Index', [
            'copy' => [
                ...AdminShell::copy(),
                ...$this->copy(),
            ],
            'filters' => $filters,
            'options' => $this->filterOptions(),
            'orders' => [
                'data' => $orders->getCollection()
                    ->map(fn (Order $order): array => $this->serializeOrder($order)),
                'pagination' => $this->pagination($orders),
            ],
            'routes' => AdminShell::routes(),
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load([
            'items.product',
            'payment',
            'payments' => fn ($query) => $query->orderBy('id'),
            'user',
        ]);

        return Inertia::render('Admin/Orders/Show', [
            'copy' => [
                ...AdminShell::copy(),
                ...$this->detailCopy(),
            ],
            'order' => $this->serializeOrderDetail($order),
            'routes' => [
                ...AdminShell::routes(),
                'updateOrder' => route('admin.orders.update', $order, absolute: false),
            ],
            'statusOptions' => $this->orderStatusOptions($order->allowedStatusUpdates()),
        ]);
    }

    public function update(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $nextStatus = (string) $request->input('status');
        if (! $order->canTransitionTo($nextStatus)) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', __('Transisi status pesanan tidak valid.'));
        }

        $order->update([
            'status' => $nextStatus,
            'admin_note' => $request->input('admin_note'),
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', __('Status pesanan berhasil diperbarui.'));
    }

    /**
     * @return array{search: string, status: string, paymentStatus: string, dateFrom: string, dateTo: string}
     */
    private function filters(Request $request): array
    {
        $status = $request->string('status')->trim()->toString();
        $paymentStatus = $request->string('payment_status')->trim()->toString();
        $dateFrom = $this->validDate($request->string('date_from')->trim()->toString());
        $dateTo = $this->validDate($request->string('date_to')->trim()->toString());

        if ($dateFrom !== '' && $dateTo !== '' && $dateTo < $dateFrom) {
            $dateTo = '';
        }

        return [
            'search' => mb_substr($request->string('q')->trim()->toString(), 0, 255),
            'status' => in_array($status, self::ORDER_STATUSES, true) ? $status : '',
            'paymentStatus' => in_array($paymentStatus, self::PAYMENT_STATUSES, true) ? $paymentStatus : '',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];
    }

    private function validDate(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date instanceof DateTimeImmutable && $date->format('Y-m-d') === $value ? $value : '';
    }

    /** @return array{orderStatuses: list<array{value: string, label: string}>, paymentStatuses: list<array{value: string, label: string}>} */
    private function filterOptions(): array
    {
        return [
            'orderStatuses' => $this->orderStatusOptions(),
            'paymentStatuses' => [
                ['value' => 'unpaid', 'label' => __('Belum ada pembayaran')],
                ['value' => 'pending', 'label' => __('Pending')],
                ['value' => 'paid', 'label' => __('Paid')],
                ['value' => 'failed', 'label' => __('Failed')],
                ['value' => 'expired', 'label' => __('Expired')],
                ['value' => 'refunded', 'label' => __('Refunded')],
            ],
        ];
    }

    /**
     * @param  list<string>  $statuses
     * @return list<array{value: string, label: string}>
     */
    private function orderStatusOptions(array $statuses = self::ORDER_STATUSES): array
    {
        return array_map(fn (string $status): array => [
            'value' => $status,
            'label' => match ($status) {
                'pending' => __('Menunggu'),
                'processing' => __('Diproses'),
                'completed' => __('Selesai'),
                'cancelled' => __('Dibatalkan'),
                default => __(strtoupper($status)),
            },
        ], $statuses);
    }

    /** @return array<string, mixed> */
    private function serializeOrder(Order $order): array
    {
        $payment = $order->getRelation('payment');
        $user = $order->getRelation('user');
        $createdAt = $order->getAttribute('created_at');
        $totalPrice = $order->getAttribute('total_price');
        $status = (string) $order->getAttribute('status');

        return [
            'id' => (int) $order->getKey(),
            'reference' => '#ORDER-'.$order->getKey(),
            'customer' => [
                'username' => $user instanceof User ? (string) $user->username : __('Pengguna tidak tersedia'),
                'email' => $user instanceof User ? (string) $user->email : '',
            ],
            'date' => Format::date($createdAt instanceof DateTimeInterface || is_string($createdAt) ? $createdAt : null),
            'total' => Format::idr(is_numeric($totalPrice) ? $totalPrice : 0),
            'payment' => ! $payment instanceof Payment ? null : [
                'label' => strtoupper((string) $payment->provider).' · '.__(strtoupper((string) $payment->status)),
                'status' => (string) $payment->status,
            ],
            'status' => [
                'label' => __(strtoupper($status)),
                'value' => $status,
            ],
            'url' => route('admin.orders.show', $order, absolute: false),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeOrderDetail(Order $order): array
    {
        $user = $order->getRelation('user');
        $payment = $order->getRelation('payment');
        /** @var Collection<int, OrderItem> $items */
        $items = $order->getRelation('items');
        /** @var Collection<int, Payment> $payments */
        $payments = $order->getRelation('payments');
        $createdAt = $order->getAttribute('created_at');
        $totalPrice = $order->getAttribute('total_price');
        $status = (string) $order->getAttribute('status');

        return [
            'adminNote' => $this->nullableString($order->getAttribute('admin_note')),
            'createdAt' => Format::dateTime($createdAt instanceof DateTimeInterface || is_string($createdAt) ? $createdAt : null),
            'customer' => [
                'email' => $user instanceof User ? (string) $user->email : '',
                'username' => $user instanceof User ? (string) $user->username : __('Pengguna tidak tersedia'),
            ],
            'id' => (int) $order->getKey(),
            'items' => $items->map(fn (OrderItem $item): array => $this->serializeOrderItem($item))->values()->all(),
            'latestPayment' => $payment instanceof Payment ? $this->serializePaymentStatus($payment) : null,
            'note' => $this->nullableString($order->getAttribute('note')),
            'payments' => $payments->map(fn (Payment $item): array => $this->serializePayment($item))->values()->all(),
            'reference' => '#ORDER-'.$order->getKey(),
            'status' => [
                'label' => __(strtoupper($status)),
                'value' => $status,
            ],
            'total' => Format::idr(is_numeric($totalPrice) ? $totalPrice : 0),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeOrderItem(OrderItem $item): array
    {
        $product = $item->getRelation('product');
        $productName = $product instanceof Souvenir ? trim((string) $product->name) : '';
        if ($productName === '') {
            $productName = trim((string) $item->getAttribute('product_name'));
        }

        $quantity = $item->getAttribute('quantity');
        $price = $item->getAttribute('price');
        $numericQuantity = is_numeric($quantity) ? (float) $quantity : 0.0;
        $numericPrice = is_numeric($price) ? (float) $price : 0.0;

        return [
            'id' => (int) $item->getKey(),
            'imageUrl' => $item->getResolvedImageUrlAttribute(),
            'name' => $productName !== '' ? $productName : __('Produk tidak tersedia'),
            'quantity' => Format::number($numericQuantity),
            'subtotal' => Format::idr($numericQuantity * $numericPrice),
            'unitPrice' => Format::idr($numericPrice),
        ];
    }

    /** @return array{label: string, status: string} */
    private function serializePaymentStatus(Payment $payment): array
    {
        $provider = strtoupper((string) $payment->getAttribute('provider'));
        $status = (string) $payment->getAttribute('status');

        return [
            'label' => $provider.' · '.__(strtoupper($status)),
            'status' => $status,
        ];
    }

    /** @return array<string, mixed> */
    private function serializePayment(Payment $payment): array
    {
        $amount = $payment->getAttribute('amount');
        $paidAt = $payment->getAttribute('paid_at');

        return [
            'amount' => Format::idr(is_numeric($amount) ? $amount : 0),
            'id' => (int) $payment->getKey(),
            'paidAt' => $paidAt instanceof DateTimeInterface || is_string($paidAt) ? Format::dateTime($paidAt) : null,
            'provider' => strtoupper((string) $payment->getAttribute('provider')),
            'reference' => $this->nullableString($payment->getAttribute('provider_ref')),
            'status' => [
                'label' => __(strtoupper((string) $payment->getAttribute('status'))),
                'value' => (string) $payment->getAttribute('status'),
            ],
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    /**
     * @param  LengthAwarePaginator<int, Order>  $orders
     * @return array<string, mixed>
     */
    private function pagination(LengthAwarePaginator $orders): array
    {
        $currentPage = $orders->currentPage();
        $lastPage = $orders->lastPage();
        $pageNumbers = array_values(array_unique(array_filter(
            [1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage],
            fn (int $page): bool => $page >= 1 && $page <= $lastPage
        )));
        sort($pageNumbers);

        return [
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'from' => $orders->firstItem(),
            'to' => $orders->lastItem(),
            'total' => $orders->total(),
            'previousUrl' => $this->relativeUrl($orders->previousPageUrl()),
            'nextUrl' => $this->relativeUrl($orders->nextPageUrl()),
            'pages' => array_map(fn (int $page): array => [
                'page' => $page,
                'url' => $this->relativeUrl($orders->url($page)) ?? $orders->url($page),
                'active' => $page === $currentPage,
            ], $pageNumbers),
            'summary' => __('Menampilkan :from–:to dari :total pesanan', [
                'from' => Format::number($orders->firstItem() ?? 0),
                'to' => Format::number($orders->lastItem() ?? 0),
                'total' => Format::number($orders->total()),
            ]),
        ];
    }

    private function relativeUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);
        if (! is_string($path)) {
            return $url;
        }

        return $path.(is_string($query) && $query !== '' ? '?'.$query : '');
    }

    /** @return array<string, string> */
    private function copy(): array
    {
        return [
            'actions' => __('Aksi'),
            'all' => __('Semua'),
            'applyFilters' => __('Terapkan Filter'),
            'customer' => __('Pelanggan'),
            'date' => __('Tanggal'),
            'dateFrom' => __('Dari'),
            'dateTo' => __('Sampai'),
            'description' => __('Cari dan pantau status pesanan serta pembayaran pelanggan dari satu daftar operasional.'),
            'detail' => __('Detail'),
            'emptyDescription' => __('Ubah atau reset filter untuk melihat pesanan lainnya.'),
            'emptyTitle' => __('Belum ada pesanan yang sesuai.'),
            'eyebrow' => __('Manajemen Order'),
            'filtersDescription' => __('Gunakan satu atau beberapa filter untuk mempersempit daftar operasional.'),
            'filtersTitle' => __('Filter Pesanan'),
            'next' => __('Berikutnya'),
            'noPayment' => __('Belum ada pembayaran'),
            'order' => __('Order'),
            'payment' => __('Pembayaran'),
            'paymentStatus' => __('Status Payment'),
            'previous' => __('Sebelumnya'),
            'reset' => __('Reset'),
            'resultsDescription' => __('Pesanan terbaru ditampilkan lebih dahulu dengan status operasional dan pembayaran terkini.'),
            'resultsTitle' => __('Hasil Pesanan'),
            'search' => __('Cari'),
            'searchPlaceholder' => __('ID order, email, atau nama'),
            'status' => __('Status'),
            'title' => __('Daftar Pesanan'),
            'total' => __('Total'),
        ];
    }

    /** @return array<string, string> */
    private function detailCopy(): array
    {
        return [
            'adminNote' => __('Catatan Admin'),
            'back' => __('Kembali ke daftar pesanan'),
            'createdOn' => __('Dibuat pada'),
            'customer' => __('Pelanggan'),
            'emptyItems' => __('Belum ada item untuk pesanan ini.'),
            'emptyPaymentsDescription' => __('Riwayat akan tampil setelah proses pembayaran dibuat oleh sistem.'),
            'emptyPaymentsTitle' => __('Belum ada pembayaran untuk pesanan ini.'),
            'eyebrow' => __('Detail Pesanan'),
            'formError' => __('Periksa kembali perubahan status pesanan.'),
            'internalNoteHelp' => __('Catatan bersifat internal dan membantu pelacakan proses pesanan.'),
            'itemsDescription' => __('Produk, jumlah, harga satuan, dan subtotal yang tercatat pada pesanan.'),
            'itemsTitle' => __('Item Pesanan'),
            'noPayment' => __('Belum ada pembayaran'),
            'notPaid' => __('Belum dibayar'),
            'orderNote' => __('Catatan Pesanan'),
            'orderTime' => __('Waktu Pesanan'),
            'paymentDescription' => __('Catatan transaksi dari provider pembayaran yang terhubung dengan pesanan.'),
            'paymentTitle' => __('Riwayat Pembayaran'),
            'referenceUnavailable' => __('Referensi belum tersedia'),
            'save' => __('Simpan Perubahan'),
            'saving' => __('Menyimpan'),
            'status' => __('Status Pesanan'),
            'subtotal' => __('Subtotal'),
            'summaryDescription' => __('Status operasional dan pembayaran terkini untuk pesanan ini.'),
            'summaryTitle' => __('Ringkasan Pesanan'),
            'title' => __('Detail Pesanan'),
            'total' => __('Total'),
            'updateDescription' => __('Pilih status berikutnya sesuai progres operasional pesanan.'),
            'updateTitle' => __('Update Status'),
        ];
    }
}
