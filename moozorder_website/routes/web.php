<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\C_produk;
use App\Http\Controllers\C_pengguna;
use App\Http\Controllers\C_kelolapesanan;
use App\Http\Controllers\LaporanPenjualanController;
use App\Http\Controllers\KategoriController; // <--- PENTING: Import KategoriController
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\PesananController;

Route::get('/', function () {
    return view('welcome');
});

// Auth
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::get('register', [AuthController::class, 'showRegister'])->name('register');
Route::post('register', [AuthController::class, 'register']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Admin Middleware Group
Route::middleware(['auth', \App\Http\Middleware\RoleMiddleware::class.':admin'])->group(function () {

    // Admin Dashboard
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Produk Routes
    Route::prefix('admin/produk')->group(function () {
        Route::get('/', [C_produk::class, 'index'])->name('produk.index');
        Route::get('/create', [C_produk::class, 'create'])->name('produk.create');
        Route::post('/store', [C_produk::class, 'store'])->name('produk.store');
        Route::get('/edit/{id_produk}', [C_produk::class, 'edit'])->name('produk.edit');
        Route::put('/update/{id_produk}', [C_produk::class, 'update'])->name('produk.update');
        Route::get('/show/{id_produk}', [C_produk::class, 'show'])->name('produk.show');
        Route::delete('/delete/{id_produk}', [C_produk::class, 'destroy'])->name('produk.destroy');
    });

    // Pengguna Routes
    Route::prefix('admin/pengguna')->group(function () {
        Route::get('/', [App\Http\Controllers\C_pengguna::class, 'index'])->name('pengguna.index');
        Route::get('/create', [App\Http\Controllers\C_pengguna::class, 'create'])->name('pengguna.create');
        Route::post('/store', [App\Http\Controllers\C_pengguna::class, 'store'])->name('pengguna.store');
        Route::get('/edit/{id}', [App\Http\Controllers\C_pengguna::class, 'edit'])->name('pengguna.edit');
        Route::post('/update/{id}', [App\Http\Controllers\C_pengguna::class, 'update'])->name('pengguna.update');
        Route::get('/show/{id}', [App\Http\Controllers\C_pengguna::class, 'show'])->name('pengguna.show');
        Route::delete('/delete/{id}', [App\Http\Controllers\C_pengguna::class, 'destroy'])->name('pengguna.destroy');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/pesanan', [C_kelolapesanan::class, 'index'])->name('pesanan.index');
        Route::put('/pesanan/status/{id}', [C_kelolapesanan::class, 'updateStatus'])->name('pesanan.updateStatus');
        Route::post('/pesanan/tambah', [C_kelolapesanan::class, 'store'])->name('pesanan.store');
        Route::get('/pesanan/detail/{id}', [C_kelolapesanan::class, 'detail'])->name('pesanan.detail');
        Route::get('/laporan/keuangan', [C_kelolapesanan::class, 'laporanKeuangan'])->name('laporan.keuangan');

        // Laporan Penjualan Routes
        Route::get('/laporan/penjualan', [C_kelolapesanan::class, 'laporanPenjualan'])->name('laporan.penjualan');
        // Add the route for the filter method
        Route::get('/laporan/penjualan/filter', [LaporanPenjualanController::class, 'filter'])->name('laporan.penjualan.filter');
        // Add the route for the exportPDF method
        Route::get('/laporan/penjualan/export-pdf', [LaporanPenjualanController::class, 'exportPDF'])->name('laporan.penjualan.exportPDF');

        // Kategori Routes
        Route::resource('kategori', App\Http\Controllers\KategoriController::class)->names([
            'index' => 'kategori.index',
            'create' => 'kategori.create',
            'store' => 'kategori.store',
            'edit' => 'kategori.edit',
            'update' => 'kategori.update',
            'destroy' => 'kategori.destroy',
        ])->except(['show']);
    });
});

// User Middleware Group
Route::middleware(['auth', \App\Http\Middleware\RoleMiddleware::class.':user'])->group(function () {
    Route::get('/user/dashboard', function () {
        return view('user.dashboard');
    })->name('user.dashboard');
});

// ====================================================================================
// Penambahan Rute untuk Integrasi Midtrans WebView (diakses dari aplikasi mobile)
// ====================================================================================

// Rute untuk memuat halaman Snap Midtrans di WebView aplikasi mobile
// Endpoint ini akan dipanggil oleh WebView di CartScreen Anda
Route::get('/payment/snap_loader', function(Request $request) {
    $snapToken = $request->query('snap_token');
    if (!$snapToken) {
        // Log error jika snap_token tidak ditemukan
        Log::error('Snap Token missing in /payment/snap_loader request', $request->all());
        return "Snap Token not found. Pembayaran tidak dapat dimuat."; // Pesan error sederhana
    }
    // Mengembalikan view snap_loader.blade.php yang akan memuat Snap.js
    // Pastikan config('midtrans.client_key') sudah diatur di config/midtrans.php
    return view('payment.snap_loader', compact('snapToken'));
});

// Rute-rute callback dari Midtrans setelah transaksi selesai/gagal/ditutup di halaman Snap
// Midtrans akan melakukan redirect ke URL ini
// Anda bisa menambahkan logika lebih kompleks di controller terpisah jika perlu
Route::get('/payment/finish', function(Request $request) {
    Log::info('Midtrans finish redirect received:', $request->all());
    // Redirect ke halaman sukses atau tampilkan pesan.
    // Jika Anda ingin deep linking kembali ke aplikasi, Anda bisa menggunakan:
    // return redirect('yourapp://payment/success?order_id=' . $request->query('order_id'));
    return "Pembayaran selesai. Anda bisa menutup halaman ini.";
});

Route::get('/payment/error', function(Request $request) {
    Log::error('Midtrans error redirect received:', $request->all());
    return "Pembayaran gagal. Silakan coba lagi.";
});

Route::get('/payment/unfinish', function(Request $request) {
    Log::warning('Midtrans unfinish redirect received:', $request->all());
    return "Pembayaran tidak diselesaikan. Silakan coba lagi.";
});
