<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\PlaceReview;
use App\Support\CacheKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, $placeId)
    {
        // 1. Validasi Input (Security First)
        $request->validate([
            'rating' => 'required|integer|min:1|max:5', // Bintang 1-5
            'comment' => 'nullable|string|max:500',     // Komentar max 500 huruf
        ]);

        // 2. Cek apakah wisata ada?
        $place = Place::findOrFail($placeId);

        // 3. Simpan Review
        PlaceReview::create([
            'place_id' => $place->id,
            'user_id' => Auth::id(), // Ambil ID user yang sedang login
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        CacheKeys::bump(CacheKeys::REVIEWS_VERSION);

        // 4. Kembali ke halaman sebelumnya
        return back()->with('success', __('Terima kasih atas ulasanmu!'));
    }
}
