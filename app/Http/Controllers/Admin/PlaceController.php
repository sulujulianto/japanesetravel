<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PlaceController extends Controller
{
    // 1. TAMPILKAN DAFTAR DATA
    public function index()
    {
        $places = Place::latest()->paginate(10);
        return view('admin.places.index', compact('places'));
    }

    // 2. TAMPILKAN FORM TAMBAH
    public function create()
    {
        return view('admin.places.create');
    }

    // 3. SIMPAN DATA BARU
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string|max:255',
            'facilities' => 'nullable|string',
            'open_days' => 'nullable|string|max:100',
            'open_hours' => 'nullable|string|max:100',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('uploads/places', 'public');
        }

        Place::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(5),
            'description' => $validated['description'],
            'image' => $imagePath,
            'address' => $validated['address'],
            'facilities' => $validated['facilities'],
            'open_days' => $validated['open_days'],
            'open_hours' => $validated['open_hours'],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.places.index')->with('success', 'Destinasi wisata berhasil ditambahkan!');
    }

    // 4. TAMPILKAN FORM EDIT (BARU)
    public function edit(Place $place)
    {
        return view('admin.places.edit', compact('place'));
    }

    // 5. UPDATE DATA (BARU)
    public function update(Request $request, Place $place)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string|max:255',
            'facilities' => 'nullable|string',
            'open_days' => 'nullable|string|max:100',
            'open_hours' => 'nullable|string|max:100',
        ]);

        // Cek jika ada gambar baru diupload
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($place->image && Storage::disk('public')->exists($place->image)) {
                Storage::disk('public')->delete($place->image);
            }
            // Simpan gambar baru
            $place->image = $request->file('image')->store('uploads/places', 'public');
        }

        // Update slug jika nama berubah
        if ($place->name !== $validated['name']) {
            $place->slug = Str::slug($validated['name']) . '-' . Str::random(5);
        }

        $place->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'address' => $validated['address'],
            'facilities' => $validated['facilities'],
            'open_days' => $validated['open_days'],
            'open_hours' => $validated['open_hours'],
            // Image sudah dihandle di atas
        ]);

        return redirect()->route('admin.places.index')->with('success', 'Data berhasil diperbarui!');
    }

    // 6. HAPUS DATA (BARU)
    public function destroy(Place $place)
    {
        // Hapus gambar dari storage agar tidak menumpuk sampah file
        if ($place->image && Storage::disk('public')->exists($place->image)) {
            Storage::disk('public')->delete($place->image);
        }

        $place->delete();

        return redirect()->route('admin.places.index')->with('success', 'Destinasi wisata telah dihapus.');
    }
}