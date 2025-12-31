<?php

namespace App\Http\Controllers;

use App\Models\Souvenir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    // 1. LIHAT KERANJANG
    public function index()
    {
        // Ambil data session 'cart' (array: id_barang => qty)
        $cart = Session::get('cart', []);

        // Ambil detail barang dari database berdasarkan ID yang ada di session
        // whereIn('id', [1, 2, ...])
        $items = Souvenir::whereIn('id', array_keys($cart))->get();

        $total = 0;
        $cartItems = [];

        foreach ($items as $item) {
            $qty = $cart[$item->id];
            $subtotal = $item->price * $qty;
            $total += $subtotal;

            // Gabungkan data barang + qty session
            $cartItems[] = [
                'product' => $item,
                'qty' => $qty,
                'subtotal' => $subtotal,
            ];
        }

        return view('cart.index', compact('cartItems', 'total'));
    }

    // 2. TAMBAH KE KERANJANG
    public function add(Request $request, $id)
    {
        $cart = Session::get('cart', []);
        
        // Jika barang sudah ada, tambah qty. Jika belum, set 1.
        if (isset($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        Session::put('cart', $cart);

        return redirect()->back()->with('success', __('Barang masuk keranjang! 🛒'));
    }

    // 3. UPDATE QUANTITY
    public function update(Request $request)
    {
        $cart = Session::get('cart', []);
        $quantities = $request->input('qty', []); // Ambil array qty dari form

        foreach ($quantities as $id => $qty) {
            if (isset($cart[$id])) {
                $cart[$id] = max(1, intval($qty)); // Minimal 1
            }
        }

        Session::put('cart', $cart);
        return redirect()->route('cart.index')->with('success', __('Keranjang diperbarui.'));
    }

    // 4. HAPUS ITEM
    public function remove($id)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            Session::put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', __('Barang dihapus.'));
    }
}
