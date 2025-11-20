<?php

namespace App\Http\Controllers;

use App\Models\Place;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    // Halaman Depan (Daftar Wisata)
    public function index()
    {
        // Ambil semua data wisata, urutkan dari yang terbaru
        $places = Place::latest()->get();
        
        // Kirim data '$places' ke tampilan 'welcome'
        return view('welcome', compact('places'));
    }

    // Halaman Detail Wisata
    // Halaman Detail Wisata
    public function show($slug)
    {
        // Cari wisata, lalu ambil juga ulasannya beserta data user penulisnya
        // with('reviews.user') => Teknik "Eager Loading" biar database tidak berat
        $place = Place::where('slug', $slug)
                      ->with(['reviews.user' => function($query) {
                          $query->latest(); // Urutkan review dari yang terbaru
                      }])
                      ->firstOrFail();
    
        return view('places.show', compact('place'));
    }
}