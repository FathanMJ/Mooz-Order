<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\M_kelolapesanan as Pesanan;
use App\Models\M_laporanpenjualan as M_laporanpenjualan;
use App\Services\FirestoreService;


class ApiC_kelolapesanan extends Controller
{
    /**
     * Mengambil daftar pesanan/notifikasi untuk pengguna yang sedang login.
     * Endpoint ini akan diakses oleh NotifikasiScreen di aplikasi mobile.
     */
    public function getNotifications(Request $request)
    {
        try {
            $userId = Auth::id();
            Log::info('Fetching notifications for user ID:', ['user_id' => $userId]);

            if (!$userId) {
                return response()->json(['error' => 'Unauthenticated or user ID not found'], 401);
            }

            // Ambil pesanan yang relevan untuk pengguna ini.
            $orders = Pesanan::where('id_pengguna', $userId)
                             ->orderBy('created_at', 'desc')
                             ->whereIn('status', ['dalam antrian', 'proses pembuatan', 'siap diambil', 'sudah diambil', 'menunggu pembayaran', 'dibatalkan'])
                             ->with(['details' => function($query) {
                                 // PERBAIKAN: Ubah 'product' menjadi 'produk' agar sesuai dengan model DetailPesanan
                                 $query->with('produk'); // <--- PERBAIKAN DI SINI
                             }])
                             ->get();

            $notifications = [];

            foreach ($orders as $order) {
                /** @var Pesanan $order */
                $orderedItems = [];
                if ($order->details && $order->details->isNotEmpty()) {
                    foreach ($order->details as $detail) {
                        $orderedItems[] = [
                            'id' => (string) $detail->id_produk,
                            // PERBAIKAN: Akses nama produk dari relasi 'produk', bukan 'product'
                            'name' => $detail->produk ? $detail->produk->nama_produk : 'Unknown Product', // <--- PERBAIKAN DI SINI
                            'price' => (float) $detail->harga_satuan,
                            'quantity' => (int) $detail->jumlah,
                            'size' => $detail->ukuran_produk ?? null,
                        ];
                    }
                }

                $displayStatus = $order->status;
                if ($order->payment_status === 'menunggu') {
                    $displayStatus = 'menunggu pembayaran';
                }

                $notifications[] = [
                    'id' => (string) $order->id,
                    'order_id' => $order->midtrans_order_id ?? null,
                    'message' => $this->generateNotificationMessage($order, $displayStatus),
                    'time' => $order->updated_at ? $order->updated_at->toIso8601String() : $order->created_at->toIso8601String(),
                    'status' => $displayStatus,
                    'orderedItems' => $orderedItems,
                    'total' => (float) $order->total_harga,
                ];
            }

            Log::info('Notifications fetched successfully for user ID:', ['user_id' => $userId, 'count' => count($notifications)]);
            return response()->json(['notifications' => $notifications], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching notifications (ApiC_kelolapesanan): ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Failed to load notifications. Please try again later.'], 500);
        }
    }

    /**
     * Helper untuk membuat pesan notifikasi berdasarkan status pesanan.
     */
    private function generateNotificationMessage(Pesanan $order, string $displayStatus): string
    {
        switch ($displayStatus) {
            case 'menunggu pembayaran':
                return 'Pesanan Anda menunggu pembayaran.';
            case 'dalam antrian':
                return 'Pesanan Anda telah masuk dalam antrian.';
            case 'proses pembuatan':
                return 'Pesanan Anda sedang diproses oleh staf kami.';
            case 'siap diambil':
                return 'Pesanan Anda siap diambil!';
            case 'sudah diambil':
                return 'Pesanan Anda telah diambil.';
            case 'dibatalkan':
                return 'Pesanan Anda telah dibatalkan.';
            default:
                return 'Update status pesanan: ' . $order->status . '.';
        }
    }


    /**
     * Mengambil daftar pesanan yang sudah selesai (dari laporan penjualan) untuk pengguna yang sedang login.
     * Endpoint ini akan diakses oleh RiwayatScreen di aplikasi mobile.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompletedOrders(Request $request)
    {
        try {
            $userId = Auth::id();
            Log::info('Fetching completed orders for user ID: ' . $userId);

            if (!$userId) {
                return response()->json(['error' => 'Unauthenticated or user ID not found'], 401);
            }

            // Ambil data dari tabel laporan_penjualan untuk pengguna ini
            $completedOrders = M_laporanpenjualan::where('id_pengguna', $userId)
                                                    // PERBAIKAN: Ubah 'product' menjadi 'produk' di with()
                                                    ->with('produk', 'pesanan.pengguna')
                                                    ->orderByDesc('waktu_diambil')
                                                    ->get();

            $formattedOrders = [];
            foreach ($completedOrders as $order) {
                $formattedOrders[] = [
                    'id'            => (string) $order->id,
                    'order_id'      => (string) $order->id_pesanan,
                    'date'          => $order->waktu_diambil ? \Carbon\Carbon::parse($order->waktu_diambil)->format('Y-m-d H:i') : 'Tanggal Tidak Tersedia',
                    // PERBAIKAN: Akses nama produk dari relasi 'produk', bukan 'product'
                    'item_name'     => $order->produk->nama_produk ?? 'Produk Dihapus',
                    'item_price'    => (float) $order->harga_satuan,
                    'item_quantity' => (int) $order->jumlah,
                    'item_total'    => (float) $order->total_harga,
                    'customer_name' => $order->pesanan->pengguna->nama ?? 'Pemesan Dihapus',
                ];
            }

            Log::info('Completed orders fetched successfully for user ID: ' . $userId . ', count: ' . count($formattedOrders));
            return response()->json(['completed_orders' => $formattedOrders], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching completed orders: ' . $e->getMessage(), ['trace' => $e->getTrace()]);
            return response()->json(['error' => 'Failed to load completed orders. Please try again later.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validasi request
            $validated = $request->validate([
                'id_pengguna' => 'required|integer',
                'items' => 'required|array',
                'total_harga' => 'required|numeric',
            ]);

            // Simpan pesanan ke MySQL
            $order = Pesanan::create([
                'id_pengguna' => $validated['id_pengguna'],
                'total_harga' => $validated['total_harga'],
                'status' => 'dalam antrian',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Ambil data item pesanan dari database
            $orderItems = $order->items->map(function($item) {
                return [
                    'id' => (string)$item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'size' => $item->size
                ];
            })->toArray();

            $notifData = [
                'order_id' => $order->id,
                'message' => 'Pesanan Anda telah masuk dalam antrian.',
                'status' => 'dalam antrian',
                'time' => now()->toIso8601String(),
                'orderedItems' => $orderItems,
                'total' => $order->total_harga,
                'catatan' => $order->catatan ?? null
            ];

            $firestore = new FirestoreService();
            $firestore->saveNotification($notifData, (string)$order->id);

            return response()->json(['message' => 'Order created successfully', 'order_id' => $order->id], 201);

        } catch (\Exception $e) {
            Log::error('Error creating order: ' . $e->getMessage(), ['trace' => $e->getTrace()]);
            return response()->json(['error' => 'Failed to create order. Please try again later.'], 500);
        }
    }
}
