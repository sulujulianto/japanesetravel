<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Souvenir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    // FUNGSI 1: Memproses Keranjang Menjadi Order
    public function process(Request $request)
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'Keranjang belanja kosong.');
        }

        // Ambil detail barang dari database
        $souvenirs = Souvenir::whereIn('id', array_keys($cart))->get();
        
        $total = 0;
        $itemsToProcess = [];

        // Cek Stok Dulu Sebelum Bikin Nota
        foreach ($souvenirs as $item) {
            $qty = $cart[$item->id];
            
            if ($item->stock < $qty) {
                return redirect()->route('cart.index')
                    ->with('error', "Stok {$item->name} kurang (Sisa: {$item->stock}). Kurangi jumlah pembelian.");
            }

            $total += $item->price * $qty;
            $itemsToProcess[] = [
                'souvenir' => $item,
                'qty' => $qty,
                'price' => $item->price
            ];
        }

        // Mulai Simpan ke Database (Pakai Transaction Biar Aman)
        DB::transaction(function () use ($total, $itemsToProcess) {
            // 1. Buat Nota Utama
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_price' => $total,
                'status' => 'pending',
                'note' => 'Pesanan Baru',
            ]);

            // 2. Masukkan Rincian Barang & Kurangi Stok
            foreach ($itemsToProcess as $data) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'souvenir_id' => $data['souvenir']->id,
                    'quantity' => $data['qty'],
                    'price' => $data['price'],
                ]);

                $data['souvenir']->decrement('stock', $data['qty']);
            }
        });

        // Kosongkan Keranjang
        Session::forget('cart');

        return redirect()->route('orders.index')->with('success', 'Pesanan Berhasil! Terima kasih.');
    }

    // FUNGSI 2: Melihat Riwayat Pesanan
    public function index()
    {
        // Ambil pesanan milik user yang sedang login
        $orders = Order::where('user_id', Auth::id())
                       ->with('items.product') // Ambil data item sekalian biar cepat
                       ->latest()
                       ->get();

        return view('orders.index', compact('orders'));
    }
}