<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Souvenir;
use App\Support\CacheKeys;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SouvenirController extends Controller
{
    // 1. DAFTAR BARANG
    public function index()
    {
        $souvenirs = Souvenir::latest()->paginate(10);

        return view('admin.souvenirs.index', compact('souvenirs'));
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
    public function destroy(Souvenir $souvenir)
    {
        Media::delete($souvenir->image);
        $souvenir->delete();

        CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);

        return redirect()->route('admin.souvenirs.index')->with('success', __('Produk berhasil dihapus.'));
    }
}
