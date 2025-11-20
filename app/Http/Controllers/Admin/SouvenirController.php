<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Souvenir;
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('uploads/souvenirs', 'public');
        }

        Souvenir::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'image' => $imagePath,
        ]);

        return redirect()->route('admin.souvenirs.index')->with('success', 'Barang berhasil ditambahkan!');
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
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

        $souvenir->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            // Image dihandle di atas
        ]);

        return redirect()->route('admin.souvenirs.index')->with('success', 'Data barang diperbarui!');
    }

    // 6. HAPUS BARANG
    public function destroy(Souvenir $souvenir)
    {
        if ($souvenir->image && Storage::disk('public')->exists($souvenir->image)) {
            Storage::disk('public')->delete($souvenir->image);
        }
        $souvenir->delete();
        return redirect()->route('admin.souvenirs.index')->with('success', 'Barang dihapus dari stok.');
    }
}