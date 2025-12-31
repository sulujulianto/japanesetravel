<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Support\CacheKeys;
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
            'name_id' => 'required|string|max:150',
            'name_en' => 'required|string|max:150',
            'description_id' => 'nullable|string|required_with:description_en',
            'description_en' => 'nullable|string|required_with:description_id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string|max:255',
            'facilities' => 'nullable|string',
            'open_days' => 'nullable|string|max:100',
            'open_hours' => 'nullable|string|max:100',
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
            $imagePath = $request->file('image')->store('uploads/places', 'public');
        }

        Place::create([
            'name' => [
                'id' => $validated['name_id'],
                'en' => $validated['name_en'],
            ],
            'slug' => Str::slug($validated['name_en']) . '-' . Str::random(5),
            'description' => $description,
            'image' => $imagePath,
            'address' => $validated['address'],
            'facilities' => $validated['facilities'],
            'open_days' => $validated['open_days'],
            'open_hours' => $validated['open_hours'],
            'created_by' => Auth::guard('admin')->id(),
        ]);

        CacheKeys::bump(CacheKeys::PLACES_VERSION);

        return redirect()->route('admin.places.index')->with('success', __('Destinasi wisata berhasil ditambahkan!'));
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
            'name_id' => 'required|string|max:150',
            'name_en' => 'required|string|max:150',
            'description_id' => 'nullable|string|required_with:description_en',
            'description_en' => 'nullable|string|required_with:description_id',
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
        if ($place->getTranslation('name', 'en') !== $validated['name_en']) {
            $place->slug = Str::slug($validated['name_en']) . '-' . Str::random(5);
        }

        $description = null;
        if (! empty($validated['description_id']) || ! empty($validated['description_en'])) {
            $description = [
                'id' => $validated['description_id'],
                'en' => $validated['description_en'],
            ];
        }

        $place->update([
            'name' => [
                'id' => $validated['name_id'],
                'en' => $validated['name_en'],
            ],
            'description' => $description,
            'address' => $validated['address'],
            'facilities' => $validated['facilities'],
            'open_days' => $validated['open_days'],
            'open_hours' => $validated['open_hours'],
            // Image sudah dihandle di atas
        ]);

        CacheKeys::bump(CacheKeys::PLACES_VERSION);

        return redirect()->route('admin.places.index')->with('success', __('Data berhasil diperbarui!'));
    }

    // 6. HAPUS DATA (BARU)
    public function destroy(Place $place)
    {
        // Hapus gambar dari storage agar tidak menumpuk sampah file
        if ($place->image && Storage::disk('public')->exists($place->image)) {
            Storage::disk('public')->delete($place->image);
        }

        $place->delete();

        CacheKeys::bump(CacheKeys::PLACES_VERSION);

        return redirect()->route('admin.places.index')->with('success', __('Destinasi wisata telah dihapus.'));
    }
}
