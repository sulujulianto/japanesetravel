<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\PlaceController;
use App\Http\Controllers\Admin\SouvenirController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController as AdminAuthenticatedSessionController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PaymentController;

// Model Data
use App\Models\Order;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Ganti Bahasa
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id'])) {
        return redirect()->back()->withCookie(cookie('locale', $locale, 60 * 24 * 30));
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
Route::delete('/cart/items/{id}', [CartController::class, 'remove'])->name('cart.items.destroy');

// ADMIN AUTH
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthenticatedSessionController::class, 'store'])
            ->middleware('throttle:admin-login')
            ->name('login.store');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
});

// AREA LOGIN (User & Admin Dashboard)
Route::middleware(['auth:web', 'verified'])->group(function () {
    
    // DASHBOARD (Diperbaiki agar mengirim data statistik)
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $data = [
            'my_orders' => Order::where('user_id', $user->id)->count(),
            'spent' => Order::where('user_id', $user->id)->whereIn('status', ['processing', 'completed'])->sum('total_price'),
            'recent_orders' => Order::where('user_id', $user->id)->with('items.product')->latest()->take(5)->get(),
        ];

        return view('dashboard', compact('data'));
    })->name('dashboard');

    // Profile, Review, Checkout
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/place/{id}/review', [ReviewController::class, 'store'])->name('review.store');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/orders', [CheckoutController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CheckoutController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/pay', [CheckoutController::class, 'pay'])->name('orders.pay');
});

// PAYMENT WEBHOOKS & RETURN URLS
Route::post('/payments/webhook/midtrans', [PaymentController::class, 'midtransWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->middleware('throttle:payments-webhook')
    ->name('payments.webhook.midtrans');
Route::post('/payments/webhook/paypal', [PaymentController::class, 'paypalWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->middleware('throttle:payments-webhook')
    ->name('payments.webhook.paypal');
Route::get('/payments/paypal/return', [PaymentController::class, 'paypalReturn'])->name('payments.paypal.return');
Route::get('/payments/paypal/cancel', [PaymentController::class, 'paypalCancel'])->name('payments.paypal.cancel');

// AREA ADMIN (Kelola Data)
Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/charts', [DashboardController::class, 'charts'])->name('dashboard.charts');
    Route::resource('orders', OrderController::class)->only(['index', 'show', 'update']);
    Route::get('inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
    Route::post('inventory/{souvenir}/restock', [InventoryController::class, 'restock'])->name('inventory.restock');
    Route::resource('places', PlaceController::class);
    Route::resource('souvenirs', SouvenirController::class);
});

require __DIR__.'/auth.php';
