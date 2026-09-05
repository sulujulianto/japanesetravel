<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Souvenir;
use App\Services\Inventory\InventoryService;
use App\Support\AdminPagination;
use App\Support\AdminShell;
use App\Support\CacheKeys;
use App\Support\Format;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SouvenirController extends Controller
{
    // 1. DAFTAR BARANG
    public function index(): Response
    {
        $souvenirs = Souvenir::latest()->orderByDesc('id')->paginate(10);

        return Inertia::render('Admin/Souvenirs/Index', [
            'copy' => [
                ...AdminShell::copy(),
                ...$this->copy(),
            ],
            'souvenirs' => [
                'data' => $souvenirs->getCollection()
                    ->map(fn (Souvenir $souvenir): array => $this->serializeSouvenir($souvenir))
                    ->values()
                    ->all(),
                'pagination' => AdminPagination::serialize($souvenirs, __('Menampilkan :from–:to dari :total souvenir', [
                    'from' => Format::number($souvenirs->firstItem() ?? 0),
                    'to' => Format::number($souvenirs->lastItem() ?? 0),
                    'total' => Format::number($souvenirs->total()),
                ])),
            ],
            'routes' => [
                ...AdminShell::routes(),
                'createSouvenir' => route('admin.souvenirs.create', absolute: false),
            ],
        ]);
    }

    // 2. FORM TAMBAH
    public function create(): Response
    {
        return Inertia::render('Admin/Souvenirs/Form', $this->formProps());
    }

    // 3. SIMPAN BARANG
    public function store(Request $request, InventoryService $inventory): RedirectResponse
    {
        $validated = $request->validate([
            'name_id' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_id' => 'required|string',
            'description_en' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048',
                Rule::dimensions()
                    ->maxWidth((int) config('media.max_width', 6000))
                    ->maxHeight((int) config('media.max_height', 6000)),
            ],
        ]);

        $description = [
            'id' => $validated['description_id'],
            'en' => $validated['description_en'],
        ];

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = Media::storeUploadedImage($request->file('image'), 'uploads/souvenirs');
        }

        DB::transaction(function () use ($description, $imagePath, $inventory, $validated): void {
            $souvenir = Souvenir::create([
                'name' => [
                    'id' => $validated['name_id'],
                    'en' => $validated['name_en'],
                ],
                'description' => $description,
                'price' => $validated['price'],
                'stock' => 0,
                'image' => $imagePath,
            ]);

            $initialStock = (int) $validated['stock'];
            if ($initialStock > 0) {
                $inventory->adjust(
                    souvenirId: (int) $souvenir->getKey(),
                    quantityDelta: $initialStock,
                    type: InventoryMovement::TYPE_INITIAL_STOCK,
                    reference: 'souvenir:'.$souvenir->getKey().':initial-stock',
                    actorId: (int) Auth::guard('admin')->id(),
                );
            }
        });

        CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);

        return redirect()->route('admin.souvenirs.index')->with('success', __('Produk berhasil ditambahkan.'));
    }

    // 4. FORM EDIT
    public function edit(Souvenir $souvenir): Response
    {
        return Inertia::render('Admin/Souvenirs/Form', $this->formProps($souvenir));
    }

    // 5. UPDATE BARANG
    public function update(
        Request $request,
        Souvenir $souvenir,
        InventoryService $inventory
    ): RedirectResponse {
        $validated = $request->validate([
            'name_id' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_id' => 'required|string',
            'description_en' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => [
                filled($souvenir->image) ? 'nullable' : 'required',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048',
                Rule::dimensions()
                    ->maxWidth((int) config('media.max_width', 6000))
                    ->maxHeight((int) config('media.max_height', 6000)),
            ],
        ]);

        if ($request->hasFile('image')) {
            $souvenir->image = Media::replaceUploadedImage($request->file('image'), $souvenir->image, 'uploads/souvenirs');
        }

        $description = [
            'id' => $validated['description_id'],
            'en' => $validated['description_en'],
        ];

        DB::transaction(function () use ($description, $inventory, $souvenir, $validated): void {
            $lockedSouvenir = Souvenir::query()
                ->whereKey($souvenir->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedSouvenir->update([
                'name' => [
                    'id' => $validated['name_id'],
                    'en' => $validated['name_en'],
                ],
                'description' => $description,
                'price' => $validated['price'],
                'image' => $souvenir->image,
            ]);

            $quantityDelta = (int) $validated['stock'] - (int) $lockedSouvenir->stock;
            if ($quantityDelta !== 0) {
                $inventory->adjust(
                    souvenirId: (int) $lockedSouvenir->getKey(),
                    quantityDelta: $quantityDelta,
                    type: InventoryMovement::TYPE_ADMIN_CORRECTION,
                    reference: 'admin:souvenir-update:'.Str::uuid(),
                    actorId: (int) Auth::guard('admin')->id(),
                );
            }
        });

        CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);

        return redirect()->route('admin.souvenirs.index')->with('success', __('Produk berhasil diperbarui.'));
    }

    // 6. HAPUS BARANG
    public function destroy(Souvenir $souvenir): RedirectResponse
    {
        Media::delete($souvenir->image);
        $souvenir->delete();

        CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);

        return redirect()->route('admin.souvenirs.index')->with('success', __('Produk berhasil dihapus.'));
    }

    /** @return array<string, mixed> */
    private function formProps(?Souvenir $souvenir = null): array
    {
        $editing = $souvenir !== null;
        $currentImageAlt = null;
        $currentImageUrl = null;
        $descriptionEn = '';
        $descriptionId = '';
        $imageRequired = true;
        $nameEn = '';
        $nameId = '';
        $price = '';
        $stock = '';

        if ($souvenir !== null) {
            $descriptionEn = (string) $souvenir->getTranslation('description', 'en');
            $descriptionId = (string) $souvenir->getTranslation('description', 'id');
            $nameEn = (string) $souvenir->getTranslation('name', 'en');
            $nameId = (string) $souvenir->getTranslation('name', 'id');
            $price = (string) $souvenir->price;
            $stock = (string) $souvenir->stock;

            if (filled($souvenir->image)) {
                $currentImageAlt = trim($souvenir->getTranslation('name', app()->getLocale()));
                $currentImageUrl = $souvenir->getImageUrlAttribute();
                $imageRequired = false;
            }
        }

        return [
            'copy' => [
                ...AdminShell::copy(),
                ...$this->formCopy($editing, $imageRequired),
            ],
            'initialValues' => [
                'currentImageAlt' => $currentImageAlt,
                'currentImageUrl' => $currentImageUrl,
                'descriptionEn' => $descriptionEn,
                'descriptionId' => $descriptionId,
                'imageRequired' => $imageRequired,
                'nameEn' => $nameEn,
                'nameId' => $nameId,
                'price' => $price,
                'stock' => $stock,
            ],
            'mode' => $editing ? 'edit' : 'create',
            'routes' => [
                ...AdminShell::routes(),
                'submitSouvenir' => $souvenir !== null
                    ? route('admin.souvenirs.update', $souvenir, absolute: false)
                    : route('admin.souvenirs.store', absolute: false),
            ],
        ];
    }

    /** @return array<string, string> */
    private function formCopy(bool $editing, bool $imageRequired): array
    {
        return [
            'cancel' => __('Batal'),
            'currentImage' => __('Gambar Saat Ini'),
            'description' => $editing
                ? __('Perbarui informasi produk, stok, harga, atau gambar yang tampil di toko.')
                : __('Lengkapi konten bilingual, harga, stok, dan gambar produk untuk katalog toko.'),
            'descriptionEn' => __('Deskripsi (EN)'),
            'descriptionHelp' => __('Jelaskan karakter produk, bahan, atau asalnya tanpa klaim promosi yang tidak tersedia.'),
            'descriptionId' => __('Deskripsi (ID)'),
            'enContentDescription' => __('Gunakan terjemahan yang natural dan konsisten dengan informasi produk.'),
            'enContentTitle' => __('Konten Bahasa Inggris'),
            'eyebrow' => __('Souvenir'),
            'formError' => __('Periksa kembali data yang belum valid.'),
            'idContentDescription' => __('Nama dan deskripsi utama untuk pelanggan berbahasa Indonesia.'),
            'idContentTitle' => __('Konten Bahasa Indonesia'),
            'media' => __('Media'),
            'mediaDescription' => $editing && ! $imageRequired
                ? __('Gambar baru akan menggantikan gambar yang sedang digunakan.')
                : __('Gunakan gambar produk yang jelas dengan komposisi sederhana.'),
            'mediaHelp' => $imageRequired
                ? __('Format gambar yang didukung: JPG, PNG, GIF, atau WebP. Maksimal 2 MB.')
                : __('Kosongkan jika tidak ingin mengganti gambar. Format JPG, PNG, GIF, atau WebP; maksimal 2 MB.'),
            'nameEn' => __('Nama Produk (EN)'),
            'nameId' => __('Nama Produk (ID)'),
            'price' => __('Harga'),
            'priceHelp' => __('Masukkan nilai harga tanpa pemisah ribuan.'),
            'salesDescription' => $editing
                ? __('Perubahan harga dan stok akan digunakan langsung oleh alur toko dan keranjang.')
                : __('Harga dan stok digunakan langsung oleh alur toko dan keranjang.'),
            'salesTitle' => __('Informasi Penjualan'),
            'saving' => __('Menyimpan'),
            'stock' => __('Stok'),
            'stockHelp' => __('Stok nol akan ditampilkan sebagai habis di katalog.'),
            'submit' => $editing ? __('Simpan Perubahan') : __('Simpan'),
            'title' => $editing ? __('Edit Souvenir') : __('Tambah Souvenir Baru'),
            'uploadImage' => __('Upload Gambar'),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeSouvenir(Souvenir $souvenir): array
    {
        $name = trim($souvenir->getTranslation('name', app()->getLocale()));
        $stock = (int) $souvenir->stock;
        $stockStatus = match (true) {
            $stock === 0 => 'out-of-stock',
            $stock <= 5 => 'low',
            default => 'available',
        };
        $stockLabel = match ($stockStatus) {
            'out-of-stock' => __('Habis'),
            'low' => __('Rendah'),
            default => __('Tersedia'),
        };

        return [
            'deleteConfirmation' => __('Yakin ingin menghapus :name? Data tidak bisa dikembalikan.', [
                'name' => $name,
            ]),
            'deleteUrl' => route('admin.souvenirs.destroy', $souvenir, absolute: false),
            'editUrl' => route('admin.souvenirs.edit', $souvenir, absolute: false),
            'id' => (int) $souvenir->getKey(),
            'imageUrl' => $souvenir->getImageUrlAttribute() ?? asset('demo/souvenir-placeholder.svg'),
            'name' => $name,
            'price' => Format::idr($souvenir->price),
            'reference' => __('SKU').' #'.$souvenir->getKey(),
            'stock' => $stock,
            'stockCount' => Format::number($stock),
            'stockLabel' => $stockLabel,
            'stockStatus' => $stockStatus,
        ];
    }

    /** @return array<string, string> */
    private function copy(): array
    {
        return [
            'actions' => __('Aksi'),
            'add' => __('Tambah Souvenir'),
            'delete' => __('Hapus'),
            'deleting' => __('Menghapus'),
            'description' => __('Kelola informasi produk, harga, stok, dan media yang tampil di toko oleh-oleh.'),
            'edit' => __('Edit'),
            'emptyDescription' => __('Tambahkan produk pertama untuk mulai mengisi toko.'),
            'emptyTitle' => __('Belum ada data souvenir.'),
            'eyebrow' => __('Master Data'),
            'image' => __('Gambar'),
            'name' => __('Nama Produk'),
            'next' => __('Berikutnya'),
            'previous' => __('Sebelumnya'),
            'price' => __('Harga'),
            'resultsDescription' => __('Pantau informasi produk dan ketersediaan stok dari satu daftar.'),
            'resultsTitle' => __('Daftar Souvenir'),
            'stock' => __('Stok'),
            'title' => __('Kelola Souvenir'),
        ];
    }
}
