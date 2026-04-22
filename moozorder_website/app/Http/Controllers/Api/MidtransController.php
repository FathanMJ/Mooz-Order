<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification; // Pastikan ini di-import untuk SDK Midtrans Notification
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\PendingOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class MidtransController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans
        // Ambil Server Key dan status Production dari konfigurasi Laravel (misal: config/midtrans.php atau .env)
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true; // Mengaktifkan sanitasi input
        Config::$is3ds = true;     // Mengaktifkan 3D Secure

        // Log konfigurasi yang digunakan (opsional, untuk debugging)
        Log::info('Midtrans Config:', [
            'serverKey' => '****' . substr(Config::$serverKey, -5), // Jangan tampilkan seluruh key
            'isProduction' => Config::$isProduction
        ]);
    }

    /**
     * Membuat transaksi dan mendapatkan Snap Token dari Midtrans.
     * Dipanggil oleh aplikasi mobile saat checkout.
     */
    public function buatTransaksi(Request $request)
    {
        try {
            // Log data request yang diterima untuk debugging
            Log::info('Request data to MidtransController@buatTransaksi:', $request->all());

            // Validasi data input dari request
            $midtransOrderId = $request->order_id;
            $items = $request->items;
            $totalAmount = $request->total_amount;
            $customerDetails = $request->customer_details;
            $catatan = $request->catatan;

            if (!$midtransOrderId || empty($items) || $totalAmount === null || empty($customerDetails)) {
                Log::error('Data tidak lengkap untuk buatTransaksi Midtrans:', $request->all());
                return response()->json([
                    'payment_status' => 'gagal',
                    'pesan' => 'Data transaksi tidak lengkap untuk Midtrans.'
                ], 400);
            }

            // Siapkan parameter transaksi untuk Midtrans Snap
            $params = [
                'transaction_details' => [
                    'order_id' => $midtransOrderId,
                    'gross_amount' => (int) $totalAmount, // Pastikan gross_amount adalah integer
                ],
                'customer_details' => $customerDetails,
                'item_details' => $items,
                'expiry' => [
                    'start_time' => date('Y-m-d H:i:s O'),
                    'unit' => 'day',
                    'duration' => 1 // Durasi kedaluwarsa Snap Token (1 hari)
                ],
                'callbacks' => [
                    // URL Callback yang akan dipanggil Midtrans setelah pembayaran selesai/gagal/unfinish
                    // Pastikan APP_URL di .env Anda adalah URL ngrok yang aktif atau domain produksi
                    'finish' => env('APP_URL') . '/payment/finish',
                    'error' => env('APP_URL') . '/payment/error',
                    'unfinish' => env('APP_URL') . '/payment/unfinish'
                ],
                'custom_field1' => $catatan ?? '', // Catatan opsional
                'custom_field2' => Auth::id(), // ID pengguna sebagai custom field (penting untuk mencocokkan)
                'enabled_payments' => [
                    'gopay', 'bank_transfer', 'shopeepay', 'qris'
                ],
            ];

            // Log parameter yang dikirim ke Midtrans untuk debugging
            Log::info('Midtrans params prepared:', $params);

            // Dapatkan Snap Token dari Midtrans
            $snapToken = Snap::getSnapToken($params);

            // Log keberhasilan pembuatan Snap Token
            Log::info('Snap Token successfully generated.', [
                'snap_token' => $snapToken,
                'order_id' => $midtransOrderId
            ]);

            // Kirim respons sukses ke aplikasi mobile
            return response()->json([
                'payment_status' => 'sukses',
                'pesan' => 'Token pembayaran berhasil dibuat',
                'data' => [
                    'snap_token' => $snapToken,
                    'order_id' => $midtransOrderId
                ]
            ]);

        } catch (\Exception $e) {
            // Tangani error jika gagal membuat Snap Token
            Log::error('Error generating Midtrans Snap Token: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'payment_status' => 'gagal',
                'pesan' => 'Gagal membuat transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menerima notifikasi webhook dari Midtrans.
     * Ini adalah endpoint utama yang dipanggil oleh server Midtrans.
     */
    public function terimaNotifikasi(Request $request)
    {
        try {
            // Langkah 1: Log raw body request yang diterima dari Midtrans
            $rawBody = $request->getContent();
            Log::info('Raw Midtrans notification body:', ['body' => $rawBody]);

            // Langkah 2: Parse body request.
            // Midtrans SDK harusnya bisa memproses php://input secara langsung,
            // tapi jika ada pembungkus 'body' dari server/middleware, kita tangani di sini.
            $notificationData = [];
            try {
                $decodedBody = json_decode($rawBody, true);
                if (isset($decodedBody['body']) && is_string($decodedBody['body'])) {
                    // Jika body terbungkus dalam key 'body' (misal dari beberapa server/proxy)
                    $notificationData = json_decode($decodedBody['body'], true);
                    Log::info('Parsed Midtrans notification from "body" key:', $notificationData);
                } else {
                    // Jika body adalah JSON langsung (standar dari Midtrans)
                    $notificationData = $decodedBody;
                    Log::info('Parsed Midtrans notification directly:', $notificationData);
                }
            } catch (\Exception $e) {
                // Log error jika parsing JSON gagal
                Log::error('Failed to decode raw notification body: ' . $e->getMessage(), ['raw_body' => $rawBody]);
                return response()->json(['error' => 'Invalid JSON format in notification body'], 400);
            }

            // Validasi dasar: pastikan data notifikasi tidak kosong dan merupakan array
            if (empty($notificationData) || !is_array($notificationData)) {
                Log::error('Parsed notification data is empty or invalid after decoding.', ['parsed_data' => $notificationData]);
                return response()->json(['error' => 'Failed to parse notification data'], 400);
            }

            // Ambil data penting dari notifikasi
            $orderId = $notificationData['order_id'] ?? null;
            $transactionStatus = $notificationData['transaction_status'] ?? null;
            $fraudStatus = $notificationData['fraud_status'] ?? null;
            $paymentType = $notificationData['payment_type'] ?? null;
            $statusCode = $notificationData['status_code'] ?? null;
            $grossAmount = $notificationData['gross_amount'] ?? null;
            $transactionId = $notificationData['transaction_id'] ?? null;


            // Validasi keberadaan order_id dan transaction_status
            if (is_null($orderId) || is_null($transactionStatus)) {
                Log::error('Notifikasi Midtrans tidak valid: order_id atau transaction_status hilang.', $notificationData);
                return response()->json(['error' => 'Invalid notification payload: missing order_id or transaction_status'], 400);
            }

            // --- PENTING: Verifikasi Signature Key untuk keamanan ---
            $receivedSignatureKey = $notificationData['signature_key'] ?? null;
            $serverKey = config('midtrans.server_key'); // Ambil Server Key dari konfigurasi Laravel

            // Pastikan semua komponen untuk perhitungan signature key ada
            if (is_null($statusCode) || is_null($grossAmount) || is_null($serverKey)) {
                Log::error('Missing data for signature key calculation.', [
                    'order_id' => $orderId,
                    'status_code' => $statusCode,
                    'gross_amount' => $grossAmount,
                    'server_key_exists' => !is_null($serverKey)
                ]);
                return response()->json(['error' => 'Server configuration error for signature verification'], 500);
            }

            // Hitung signature key di sisi server Anda
            $calculatedSignatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            // Bandingkan signature key yang diterima dengan yang dihitung
            if ($receivedSignatureKey !== $calculatedSignatureKey) {
                Log::error('Signature key mismatch detected!', [
                    'order_id' => $orderId,
                    'received' => $receivedSignatureKey,
                    'calculated' => $calculatedSignatureKey
                ]);
                return response()->json(['error' => 'Invalid Signature Key'], 403); // Status 403 Forbidden
            }
            Log::info('Signature key verified successfully for order: ' . $orderId);
            // --- Akhir Verifikasi Signature Key ---


            // Cari pesanan di tabel PendingOrder
            $pendingOrder = PendingOrder::where('midtrans_order_id', $orderId)->first();

            // Jika pendingOrder tidak ditemukan
            if (!$pendingOrder) {
                // Cek apakah pesanan sudah ada di tabel Pesanan (mungkin notifikasi duplikat)
                $pesananExist = Pesanan::where('midtrans_order_id', $orderId)->first();
                if ($pesananExist) {
                    Log::info('Notifikasi diterima untuk order yang sudah diproses dan final (ID: ' . $orderId . '). Status: ' . $pesananExist->status);
                    // Kembalikan 200 OK agar Midtrans tidak terus mengirim notifikasi
                    return response()->json(['status' => 'success', 'pesan' => 'Order sudah diproses sebelumnya'], 200);
                }
                // Jika tidak ada di pending dan tidak ada di Pesanan
                Log::warning('Notifikasi diterima untuk order yang tidak dikenal atau belum di-pending: ' . $orderId);
                // Kembalikan 200 OK agar Midtrans tidak terus mengirim notifikasi
                return response()->json(['status' => 'success', 'pesan' => 'Order tidak ditemukan di pending list, mungkin sudah final.'], 200);
            }

            // Mulai transaksi database
            DB::beginTransaction();
            try {
                // Logika pemrosesan status transaksi
                if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                    // Pembayaran berhasil
                    if ($fraudStatus == 'accept' || $fraudStatus === null) {
                        // Jika status fraud adalah 'accept' atau tidak ada (aman)
                        // Cek lagi untuk duplikasi di tabel Pesanan (sangat penting jika notifikasi terlambat datang)
                        $pesananExist = Pesanan::where('midtrans_order_id', $orderId)->first();
                        if ($pesananExist) {
                            Log::info('Webhook: Order already in Pesanan table. Not creating duplicate.', ['order_id' => $orderId]);
                            DB::commit(); // Pastikan commit jika return di sini
                            return response()->json(['status' => 'success', 'pesan' => 'Order sudah ada di tabel pesanan'], 200);
                        }

                        // Buat entri baru di tabel Pesanan
                        $pesanan = Pesanan::create([
                            'id_pengguna' => $pendingOrder->user_id,
                            'total_harga' => (int) $pendingOrder->total_amount,
                            'status' => 'dalam antrian',
                            'payment_status' => 'lunas',
                            'payment_type' => $paymentType,
                            'catatan' => $pendingOrder->catatan,
                            'midtrans_order_id' => $orderId,
                            'transaction_id' => $transactionId,
                            'gross_amount' => (int)($grossAmount),
                            'nama_pemesan' => $pendingOrder->user ? $pendingOrder->user->nama : 'Pengguna Dihapus',
                        ]);

                        // Decode item details dari pending order dan simpan ke DetailPesanan
                        $items = json_decode($pendingOrder->item_details, true);
                        if (!empty($items)) {
                            foreach ($items as $item) {
                                $subtotalItem = ($item['quantity'] ?? 1) * ($item['price'] ?? 0);
                                DetailPesanan::create([
                                    'id_pesanan' => $pesanan->id,
                                    'id_produk' => $item['id'],
                                    'jumlah' => $item['quantity'] ?? 1,
                                    'harga_satuan' => $item['price'] ?? 0,
                                    'subtotal' => (int) $subtotalItem,
                                    'ukuran_produk' => $item['size'] ?? null,
                                    'catatan' => $item['catatan'] ?? null
                                ]);
                            }
                        }

                        // Hapus order dari tabel PendingOrder setelah berhasil diproses
                        $pendingOrder->delete();
                        Log::info('Order successfully moved from pending to Pesanan.', ['order_id' => $orderId]);

                    } elseif ($fraudStatus == 'challenge') {
                        // Pembayaran berstatus 'challenge' (perlu verifikasi manual)
                        Log::warning('Midtrans notification: transaction challenge', ['order_id' => $orderId]);
                        // Anda bisa memperbarui status pendingOrder atau membuat Pesanan dengan status 'challenge'
                        $pendingOrder->update(['status' => 'challenge', 'payment_status' => 'pending_fraud']);
                        Log::info('Pending order status updated to challenge.', ['order_id' => $orderId]);
                    }

                } elseif (in_array($transactionStatus, ['cancel', 'expire', 'deny', 'refund', 'partial_refund'])) {
                    // Transaksi dibatalkan, kadaluarsa, ditolak, atau direfund
                    Log::info('Midtrans notification: transaction status ' . $transactionStatus, ['order_id' => $orderId]);
                    if ($pendingOrder) {
                        $pendingOrder->delete(); // Hapus dari pending list
                        Log::info('Pending order deleted due to transaction status: ' . $transactionStatus, ['order_id' => $orderId]);
                    }
                    // Jika order sudah ada di tabel Pesanan (misalnya dari settlement lalu di-refund)
                    $pesananExist = Pesanan::where('midtrans_order_id', $orderId)->first();
                    if ($pesananExist) {
                        $pesananExist->update([
                            'status' => 'dibatalkan', // Atau 'gagal'
                            'payment_status' => 'gagal'
                        ]);
                        Log::info('Pesanan status updated due to transaction status: ' . $transactionStatus, ['order_id' => $orderId]);
                    }

                } elseif ($transactionStatus == 'pending') {
                    // Transaksi masih menunggu pembayaran
                    Log::info('Midtrans notification: transaction status pending', ['order_id' => $orderId]);
                    // Opsional: perbarui status di pendingOrder agar aplikasi mobile bisa menampilkan 'menunggu pembayaran'
                    if ($pendingOrder) {
                        $pendingOrder->update(['status' => 'menunggu pembayaran', 'payment_status' => 'menunggu']);
                        Log::info('Pending order status updated to pending payment.', ['order_id' => $orderId]);
                    }
                }

                DB::commit(); // Commit transaksi database jika semua berhasil
                return response()->json(['status' => 'success', 'message' => 'Notification processed'], 200);

            } catch (\Exception $e) {
                DB::rollBack(); // Rollback jika ada error dalam transaksi database
                Log::error('Database transaction failed for notification: ' . $e->getMessage(), [
                    'order_id' => $orderId,
                    'trace' => $e->getTraceAsString(),
                    'notification_data' => $notificationData // Log notifikasi yang menyebabkan error
                ]);
                // Kembalikan respons error 500
                return response()->json(['error' => 'Error processing notification: ' . $e->getMessage()], 500);
            }

        } catch (\Exception $e) {
            // Tangani error yang terjadi di luar blok DB::transaction (misal error parsing awal)
            Log::error('Fatal error processing Midtrans notification: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_content' => $request->getContent() // Log konten request asli
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
