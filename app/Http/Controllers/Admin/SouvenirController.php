<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Souvenir;
use App\Support\CacheKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
            $imagePath = $request->file('image')->store('uploads/souvenirs', 'public');
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

        return redirect()->route('admin.souvenirs.index')->with('success', __('Barang berhasil ditambahkan!'));
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Hapus gambar lama
            if ($souvenir->image && Storage::disk('public')->exists($souvenir->image)) {
                Storage::disk('public')->delete($souvenir->image);
            }
            // Upload baru
            $souvenir->image = $request->file('image')->store('uploads/souvenirs', 'public');
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
            // Image dihandle di atas
        ]);

        CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);

        return redirect()->route('admin.souvenirs.index')->with('success', __('Data barang diperbarui!'));
    }

    // 6. HAPUS BARANG
    public function destroy(Souvenir $souvenir)
    {
        if ($souvenir->image && Storage::disk('public')->exists($souvenir->image)) {
            Storage::disk('public')->delete($souvenir->image);
        }
        $souvenir->delete();

        CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);

        return redirect()->route('admin.souvenirs.index')->with('success', __('Barang dihapus dari stok.'));
    }
}
