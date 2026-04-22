<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\MidtransController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ApiC_kelolapesanan;


Route::post('register', [ApiAuthController::class, 'register']);
Route::post('login', [ApiAuthController::class, 'login']);
Route::post('send-otp', [ApiAuthController::class, 'sendOtp']);
Route::post('reset-password', [ApiAuthController::class, 'resetPassword']);


Route::prefix('auth')->middleware('auth:api')->group(function () {
    Route::post('logout', [ApiAuthController::class, 'logout']);
    Route::post('refresh', [ApiAuthController::class, 'refresh']);
    Route::get('me', [ApiAuthController::class, 'me']);
    Route::post('change-password', [ApiAuthController::class, 'changePassword']);
});


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/produk', [ProdukController::class, 'index']);
Route::get('/produk/{id_produk}', [ProdukController::class, 'show']);
Route::post('/produk', [ProdukController::class, 'store']);
Route::put('/produk/{id_produk}', [ProdukController::class, 'update']);
Route::delete('/produk/{id_produk}', [ProdukController::class, 'destroy']);



Route::post('payment/notification', [MidtransController::class, 'terimaNotifikasi']);


Route::prefix('payment')->middleware('auth:api')->group(function () {

    Route::post('checkout', [CheckoutController::class, 'checkout']);


    Route::post('create-transaction', [MidtransController::class, 'buatTransaksi']);
});


// PERBAIKAN DI SINI: Tambahkan $ pada variabel id
Route::get('/debug/product/{id}', function($id) {
    return \App\Models\Produk::find($id) ?? // <-- PERBAIKAN: Ubah 'id' menjadi '$id'
           response()->json(['error' => 'Product not found'], 404);
})->middleware('auth:api');


Route::middleware('auth:api')->group(function () {
    Route::get('/notifications', [ApiC_kelolapesanan::class, 'getNotifications']);
    // Rute baru untuk riwayat pesanan yang sudah selesai
    Route::get('/orders/completed', [ApiC_kelolapesanan::class, 'getCompletedOrders']);
});

\Log::info('ORDER ITEMS:', $orderItems);
