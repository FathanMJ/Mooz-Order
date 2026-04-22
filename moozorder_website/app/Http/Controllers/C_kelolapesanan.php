<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\M_kelolapesanan;
use App\Models\M_detailpesanan;
use App\Models\M_laporanpenjualan;
use App\Models\M_pengguna; // Pastikan ini diimpor untuk relasi 'pengguna'

class C_kelolapesanan extends Controller
{
    /**
     * Menampilkan daftar semua pesanan.
     * Memuat relasi 'details.produk' dan 'pengguna' untuk ditampilkan.
     */
    public function index()
    {
        // Mengambil semua pesanan dengan detail produk dan informasi pengguna terkait,
        // diurutkan dari yang terbaru dibuat.
        $pesanan = M_kelolapesanan::with('details.produk', 'pengguna')->orderByDesc('created_at')->get();
        return view('admin.v_KelolaPesanan', compact('pesanan'));
    }

    /**
     * Memperbarui status pesanan dan memindahkan data ke laporan penjualan jika status 'sudah diambil' atau 'selesai'.
     */
    public function updateStatus(Request $request, $id)
    {
        // Memulai transaksi database untuk memastikan semua operasi berhasil atau tidak sama sekali (atomisitas).
        DB::beginTransaction();

        try {
            // Mencari pesanan berdasarkan ID, dengan memuat detail pesanan.
            $pesanan = M_kelolapesanan::with('details')->findOrFail($id);

            // Memperbarui status pesanan.
            $pesanan->status = $request->status;
            $pesanan->save();

            // Jika status pesanan adalah 'sudah diambil' atau 'selesai',
            // maka data pesanan dan detailnya akan dipindahkan ke tabel laporan_penjualan
            // dan dihapus dari tabel pesanan serta detail_pesanan.
            if (in_array($request->status, ['sudah diambil', 'selesai'])) {
                $waktuAmbil = now(); // Mendapatkan waktu saat ini.

                // Iterasi setiap detail pesanan untuk membuat entri terpisah di laporan_penjualan.
                foreach ($pesanan->details as $detail) {
                    M_laporanpenjualan::create([
                        'id_pesanan'    => $pesanan->id,
                        'id_produk'     => $detail->id_produk,
                        'jumlah'        => $detail->jumlah,
                        'harga_satuan'  => $detail->harga_satuan,
                        'total_harga'   => $detail->jumlah * $detail->harga_satuan,
                        'waktu_diambil' => $waktuAmbil,
                        'id_pengguna'   => $pesanan->id_pengguna, // Menyertakan ID pengguna dari pesanan asli
                    ]);
                }

                // Setelah data dipindahkan, hapus detail pesanan dari tabel detail_pesanan.
                $pesanan->details()->delete();
                // Kemudian, hapus pesanan dari tabel pesanan.
                $pesanan->delete();
            }

            // Commit transaksi jika semua operasi berhasil.
            DB::commit();
            return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui.');

        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan untuk membatalkan semua operasi.
            DB::rollBack();
            // Mengembalikan ke halaman sebelumnya dengan pesan error dan detail kesalahan.
            return redirect()->back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    /**
     * Menyimpan pesanan baru. Asumsi pesanan ini langsung dianggap 'sudah diambil'.
     */
    public function store(Request $request)
    {
        // Memulai transaksi database.
        DB::beginTransaction();

        try {
            // Membuat pesanan baru. Karena diasumsikan langsung 'sudah diambil',
            // statusnya langsung disetel demikian.
            $pesanan = M_kelolapesanan::create([
                'id_pengguna' => $request->id_pengguna,
                'status'      => 'sudah diambil', // Asumsi pesanan dibuat dan langsung diambil
                'catatan'     => $request->catatan,
            ]);

            // Menambahkan detail produk untuk pesanan baru.
            foreach ($request->produk as $item) {
                $pesanan->details()->create([
                    'id_produk'      => $item['id_produk'],
                    'jumlah'         => $item['jumlah'],
                    'harga_satuan'   => $item['harga_satuan']
                ]);
            }

            // Karena statusnya langsung 'sudah diambil', langsung masukkan ke laporan penjualan.
            $waktuAmbil = now();

            foreach ($pesanan->details as $detail) {
                M_laporanpenjualan::create([
                    'id_pesanan'    => $pesanan->id,
                    'id_produk'     => $detail->id_produk,
                    'jumlah'        => $detail->jumlah,
                    'harga_satuan'  => $detail->harga_satuan,
                    'total_harga'   => $detail->jumlah * $detail->harga_satuan,
                    'waktu_diambil' => $waktuAmbil,
                    'id_pengguna'   => $pesanan->id_pengguna, // Menyertakan ID pengguna
                ]);
            }

            // Hapus detail dan pesanan asli setelah dimasukkan ke laporan penjualan.
            $pesanan->details()->delete();
            $pesanan->delete();

            // Commit transaksi.
            DB::commit();
            return redirect()->route('admin.pesanan.index')->with('success', 'Pesanan berhasil ditambahkan dan diselesaikan.');

        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan.
            DB::rollBack();
            return redirect()->route('admin.pesanan.index')->with('error', 'Gagal menyimpan pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail dari pesanan tertentu.
     */
    public function detail($id)
    {
        // Mengambil detail pesanan tertentu beserta informasi produk.
        $pesanan = M_kelolapesanan::with('details.produk')->findOrFail($id);
        return view('admin.v_DetailPesanan', compact('pesanan'));
    }

    /**
     * Menampilkan laporan penjualan.
     */
    public function laporanPenjualan()
    {
        // Mengambil semua laporan penjualan dengan informasi produk, pesanan (jika masih ada),
        // dan pengguna terkait, diurutkan dari ID pesanan terbaru.
        $laporan = M_laporanpenjualan::with('produk', 'pesanan', 'pengguna')->orderByDesc('id_pesanan')->get();
        return view('admin.v_LaporanPenjualan', compact('laporan'));
    }
}
