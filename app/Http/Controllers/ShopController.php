<?php

namespace App\Http\Controllers;

use App\Models\Souvenir;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    // Halaman Katalog Toko
    public function index()
    {
        // Ambil barang terbaru, 12 barang per halaman
        $souvenirs = Souvenir::latest()->paginate(12);
        
        return view('shop.index', compact('souvenirs'));
    }
}