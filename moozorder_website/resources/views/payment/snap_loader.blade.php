<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Rute untuk memuat halaman Snap Midtrans di WebView
Route::get('/payment/snap_loader', function(Request $request) {
    $snapToken = $request->query('snap_token');
    if (!$snapToken) {
        return "Snap Token not found"; // Handle error jika snap_token tidak ada
    }
    // Pastikan config('midtrans.client_key') sudah diatur di config/midtrans.php
    return view('payment.snap_loader', compact('snapToken'));
});

// Anda bisa menambahkan rute untuk finish, error, unfinish redirect dari Midtrans
// Contoh sederhana:
Route::get('/payment/finish', function(Request $request) {
    // Anda bisa log request ini atau mengarahkan ke halaman default di aplikasi Anda
    Log::info('Midtrans finish redirect received:', $request->all());
    // Biasanya di sini Anda bisa mengarahkan kembali ke aplikasi mobile dengan deep linking
    // Contoh: return redirect('yourapp://payment/success');
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
