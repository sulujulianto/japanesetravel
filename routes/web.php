<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\PlaceController;
use App\Http\Controllers\Admin\SouvenirController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ReviewController;

// Model Data
use App\Models\Order;
use App\Models\Souvenir;
use App\Models\Place;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Ganti Bahasa
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id'])) {
        Session::put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

// Halaman Publik
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/place/{slug}', [HomeController::class, 'show'])->name('place.show');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');

// Keranjang
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::get('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

// AREA LOGIN (User & Admin Dashboard)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // DASHBOARD (Diperbaiki agar mengirim data statistik)
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $data = [];

        if ($user->role === 'admin') {
            // DATA UNTUK ADMIN
            $data = [
                'revenue' => Order::where('status', 'completed')->sum('total_price'),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'total_products' => Souvenir::count(),
                'total_places' => Place::count(),
                'recent_orders' => Order::with('user')->latest()->take(5)->get(),
                'low_stock' => Souvenir::where('stock', '<', 5)->get()
            ];
        } else {
            // DATA UNTUK USER
            $data = [
                'my_orders' => Order::where('user_id', $user->id)->count(),
                'spent' => Order::where('user_id', $user->id)->where('status', 'completed')->sum('total_price'),
                'recent_orders' => Order::where('user_id', $user->id)->with('items.product')->latest()->take(5)->get()
            ];
        }

        return view('dashboard', compact('data'));
    })->name('dashboard');

    // Profile, Review, Checkout
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/place/{id}/review', [ReviewController::class, 'store'])->name('review.store');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/orders', [CheckoutController::class, 'index'])->name('orders.index');
});

// AREA ADMIN (Kelola Data)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('places', PlaceController::class);
    Route::resource('souvenirs', SouvenirController::class);
});

require __DIR__.'/auth.php';