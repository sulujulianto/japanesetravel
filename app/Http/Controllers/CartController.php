<?php

namespace App\Http\Controllers;

use App\Models\Souvenir;
use App\Models\User;
use App\Support\CheckoutIdempotency;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function __construct(private readonly CheckoutIdempotency $checkoutIdempotency) {}

    // 1. LIHAT KERANJANG
    public function index()
    {
        // Ambil data session 'cart' (array: id_barang => qty)
        $cart = Session::get('cart', []);
        [$cart, $changed] = $this->sanitizeCart($cart);
        if ($changed) {
            Session::put('cart', $cart);
            $this->checkoutIdempotency->forget();
        }

        // Ambil detail barang dari database berdasarkan ID yang ada di session
        // whereIn('id', [1, 2, ...])
        $items = Souvenir::whereIn('id', array_keys($cart))->get()->keyBy('id');

        $total = 0;
        $cartItems = [];

        foreach ($cart as $id => $qty) {
            $item = $items->get((int) $id);
            if (! $item) {
                continue;
            }
            $subtotal = $item->price * $qty;
            $total += $subtotal;

            // Gabungkan data barang + qty session
            $cartItems[] = [
                'product' => $item,
                'qty' => $qty,
                'subtotal' => $subtotal,
            ];
        }

        /** @var Collection<int, \App\Models\UserAddress> $addresses */
        $addresses = collect();
        $user = Auth::guard('web')->user();

        if ($user instanceof User) {
            $addresses = $user->addresses()
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->get();
        }

        $checkoutToken = $user instanceof User && ! empty($cartItems)
            ? $this->checkoutIdempotency->issue()
            : null;

        if ($checkoutToken === null) {
            $this->checkoutIdempotency->forget();
        }

        return view('cart.index', compact('addresses', 'cartItems', 'checkoutToken', 'total'));
    }

    // 2. TAMBAH KE KERANJANG
    public function add(Request $request, $id)
    {
        $souvenir = Souvenir::find($id);
        if (! $souvenir) {
            return redirect()->back()->with('error', __('Produk tidak ditemukan.'));
        }

        if ($souvenir->stock <= 0) {
            return redirect()->back()->with('error', __('Produk sedang habis.'));
        }

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $requestedQty = (int) ($validated['quantity'] ?? 1);
        $cart = Session::get('cart', []);
        $currentQty = (int) ($cart[$souvenir->id] ?? 0);
        $targetQty = $currentQty + $requestedQty;
        $finalQty = min($targetQty, (int) $souvenir->stock);

        if ($finalQty < 1) {
            return redirect()->back()->with('error', __('Produk sedang habis.'));
        }

        $cart[$souvenir->id] = $finalQty;

        Session::put('cart', $cart);
        $this->checkoutIdempotency->forget();

        if ($finalQty < $targetQty) {
            return redirect()->back()->with('error', __('Jumlah di keranjang disesuaikan dengan stok tersedia.'));
        }

        return redirect()->back()->with('success', __('Produk ditambahkan ke keranjang.'));
    }

    // 3. UPDATE QUANTITY
    public function update(Request $request)
    {
        $cart = Session::get('cart', []);
        [$cart, $changedBySanitizer] = $this->sanitizeCart($cart);
        $quantities = $request->input('qty', []); // Ambil array qty dari form
        $warnings = [];

        $souvenirs = Souvenir::whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        foreach ($quantities as $id => $qty) {
            $id = (int) $id;
            if (! isset($cart[$id])) {
                continue;
            }

            $souvenir = $souvenirs->get($id);
            if (! $souvenir || $souvenir->stock <= 0) {
                unset($cart[$id]);
                $warnings[] = __('Sebagian produk tidak tersedia dan telah dihapus dari keranjang.');

                continue;
            }

            $normalizedQty = max(1, (int) $qty);
            $clampedQty = min($normalizedQty, (int) $souvenir->stock);
            if ($clampedQty !== $normalizedQty) {
                $warnings[] = __('Sebagian jumlah barang disesuaikan dengan stok tersedia.');
            }

            $cart[$id] = $clampedQty;
        }

        Session::put('cart', $cart);
        $this->checkoutIdempotency->forget();

        if ($changedBySanitizer || ! empty($warnings)) {
            return redirect()->route('cart.index')->with('error', __('Keranjang diperbarui dengan penyesuaian stok.'));
        }

        return redirect()->route('cart.index')->with('success', __('Keranjang diperbarui.'));
    }

    // 4. HAPUS ITEM
    public function remove($id)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            Session::put('cart', $cart);
            $this->checkoutIdempotency->forget();
        }

        return redirect()->route('cart.index')->with('success', __('Produk dihapus dari keranjang.'));
    }

    /**
     * @param  array<int|string, int|string>  $cart
     * @return array{0: array<int, int>, 1: bool}
     */
    private function sanitizeCart(array $cart): array
    {
        if (empty($cart)) {
            return [$cart, false];
        }

        $changed = false;
        $souvenirs = Souvenir::whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');
        $sanitized = [];

        foreach ($cart as $id => $qty) {
            $id = (int) $id;
            $souvenir = $souvenirs->get($id);
            if (! $souvenir || $souvenir->stock <= 0) {
                $changed = true;

                continue;
            }

            $normalizedQty = max(1, (int) $qty);
            $clampedQty = min($normalizedQty, (int) $souvenir->stock);
            if ($clampedQty !== $qty) {
                $changed = true;
            }

            $sanitized[$id] = $clampedQty;
        }

        if (count($sanitized) !== count($cart)) {
            $changed = true;
        }

        return [$sanitized, $changed];
    }
}
