<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Souvenir;
use App\Support\AdminPagination;
use App\Support\AdminShell;
use App\Support\CacheKeys;
use App\Support\Format;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function create()
    {
        return view('admin.souvenirs.create');
    }

    // 3. SIMPAN BARANG
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_id' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_id' => 'nullable|string|required_with:description_en',
            'description_en' => 'nullable|string|required_with:description_id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048',
                Rule::dimensions()
                    ->maxWidth((int) config('media.max_width', 6000))
                    ->maxHeight((int) config('media.max_height', 6000)),
            ],
        ]);

        $description = null;
        if (! empty($validated['description_id']) || ! empty($validated['description_en'])) {
            $description = [
                'id' => $validated['description_id'],
                'en' => $validated['description_en'],
            ];
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = Media::storeUploadedImage($request->file('image'), 'uploads/souvenirs');
        }

        Souvenir::create([
            'name' => [
                'id' => $validated['name_id'],
                'en' => $validated['name_en'],
            ],
            'description' => $description,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'image' => $imagePath,
        ]);

        CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);

        return redirect()->route('admin.souvenirs.index')->with('success', __('Produk berhasil ditambahkan.'));
    }

    // 4. FORM EDIT
    public function edit(Souvenir $souvenir)
    {
        return view('admin.souvenirs.edit', compact('souvenir'));
    }

    // 5. UPDATE BARANG
    public function update(Request $request, Souvenir $souvenir)
    {
        $validated = $request->validate([
            'name_id' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_id' => 'nullable|string|required_with:description_en',
            'description_en' => 'nullable|string|required_with:description_id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => [
                'nullable',
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

        $description = null;
        if (! empty($validated['description_id']) || ! empty($validated['description_en'])) {
            $description = [
                'id' => $validated['description_id'],
                'en' => $validated['description_en'],
            ];
        }

        $souvenir->update([
            'name' => [
                'id' => $validated['name_id'],
                'en' => $validated['name_en'],
            ],
            'description' => $description,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'image' => $souvenir->image,
        ]);

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
