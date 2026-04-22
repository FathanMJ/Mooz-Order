<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; // Pastikan ini di-import untuk Str::uuid()
use App\Models\PendingOrder;
use App\Http\Controllers\Api\MidtransController; // Import MidtransController
use Illuminate\Database\QueryException; // Import QueryException untuk penanganan error database

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        try {
            Log::info('Checkout Request:', $request->all());

            // Validasi input request dari frontend
            $validator = Validator::make($request->all(), [
                'items' => 'required|array|min:1',
                'total_amount' => 'required|numeric|min:1',
                'customer_details' => 'required|array',
                'customer_details.first_name' => 'required|string',
                'customer_details.email' => 'required|email',
                'customer_details.phone' => 'required|string',
                'catatan' => 'nullable|string',
                'user_id' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                Log::error('Checkout validation error:', $validator->errors()->toArray());
                return response()->json([
                    'payment_status' => 'gagal',
                    'pesan' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }

            // PERBAIKAN: Generate order_id yang sangat unik menggunakan Str::uuid()
            // Ini akan menghasilkan UUID (Universally Unique Identifier) yang hampir pasti unik.
            $orderId = 'ORDER-' . Str::uuid()->toString(); // toString() untuk mendapatkan string dari UUID object

            // Simpan ke tabel pending_orders (Ini adalah penyimpanan PERTAMA dan SATU-SATUNYA di flow ini)
            $pendingOrder = PendingOrder::create([
                'user_id' => $request->user_id,
                'midtrans_order_id' => $orderId,
                'item_details' => json_encode($request->items),
                'total_amount' => $request->total_amount,
                'catatan' => $request->catatan ?? null
            ]);

            Log::info('Pending Order created:', [
                'pending_order_id' => $pendingOrder->id,
                'midtrans_order_id' => $orderId,
                'user_id' => $request->user_id
            ]);

            // Kirim request ke MidtransController untuk mendapatkan snap_token
            // MidtransController akan menggunakan orderId yang baru saja dibuat ini.
            $midtransController = new MidtransController();
            $midtransRequest = new Request([
                'order_id' => $orderId, // Gunakan orderId yang baru saja dibuat
                'items' => $request->items,
                'total_amount' => $request->total_amount,
                'customer_details' => $request->customer_details,
                'catatan' => $request->catatan ?? null // Teruskan catatan juga ke MidtransController
            ]);

            // Panggil method buatTransaksi di MidtransController
            return $midtransController->buatTransaksi($midtransRequest);

        } catch (QueryException $e) {
            // Menangkap error database secara spesifik (misalnya duplicate entry)
            if ($e->getCode() == 23000) { // Kode SQLSTATE untuk Integrity constraint violation
                Log::error('Checkout Failed due to Duplicate Order ID (QueryException): ' . $e->getMessage(), [
                    'order_id_attempted' => $orderId ?? 'not_generated',
                    'request_data' => $request->all(),
                    'trace' => $e->getTrace()
                ]);
                return response()->json([
                    'payment_status' => 'gagal',
                    'pesan' => 'Maaf, terjadi masalah saat memproses transaksi Anda karena ID transaksi sudah digunakan. Silakan coba lagi. (ERR-DUP-ID)',
                ], 409); // 409 Conflict
            }
            // Jika ada QueryException lain (misalnya masalah koneksi DB, sintaks SQL salah, dll)
            Log::error('Checkout Failed with unexpected database error: ' . $e->getMessage(), [
                'trace' => $e->getTrace()
            ]);
            return response()->json([
                'payment_status' => 'gagal',
                'pesan' => 'Checkout gagal karena masalah database. Silakan coba lagi.',
            ], 500);

        } catch (\Exception $e) {
            // Menangkap semua jenis error lainnya
            Log::error('Checkout Failed with general error: ' . $e->getMessage(), [
                'trace' => $e->getTrace()
            ]);

            return response()->json([
                'payment_status' => 'gagal',
                'pesan' => 'Checkout gagal. Silakan coba lagi. (ERR-GENERAL)',
            ], 500); // 500 Internal Server Error
        }
    }
}
