<?php

use App\Http\Controllers\Shop\AuthController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\PaymentController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Shop\StyleguideController;
use App\Http\Controllers\Shop\TrackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Klik.Cake Shop
|--------------------------------------------------------------------------
*/

// Screen 01: Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Screen 02: Katalog / Koleksi
Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/katalog/{slug}', [CatalogController::class, 'index'])->name('catalog.category');

// Screen 03: Detail Produk & Slot Availability API
Route::get('/produk/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/api/slots', [ProductController::class, 'getSlots'])->name('api.slots');

// Screen 04: Keranjang (Session based)
Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang/tambah', [CartController::class, 'add'])->name('cart.add');
Route::patch('/keranjang/update/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/keranjang/hapus/{key}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/keranjang/catatan', [CartController::class, 'updateNotes'])->name('cart.notes');

// Screen 05: Checkout
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// Screen 06: Pembayaran
Route::get('/pembayaran/{orderNumber}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/pembayaran/{orderNumber}/status', [PaymentController::class, 'checkStatus'])
    ->name('payment.status')
    ->middleware('throttle:20,1');

// Screen 07: Konfirmasi & Lacak Pesanan (Protected by rate limiting against brute force)
Route::get('/lacak', [TrackController::class, 'index'])
    ->name('track.index')
    ->middleware('throttle:30,1');
Route::get('/lacak/{orderNumber}', [TrackController::class, 'show'])
    ->name('track.show')
    ->middleware('throttle:30,1');

// User & Admin Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/akun', [AuthController::class, 'dashboard'])->name('account.dashboard');
});

// Styleguide (local environment only)
Route::get('/styleguide', [StyleguideController::class, 'index'])->name('styleguide');

// Payment Webhook Routes
require __DIR__.'/webhooks.php';
