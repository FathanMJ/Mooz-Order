<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Pastikan ini diimpor jika ingin menggunakan Factory

class Pesanan extends Model
{
    use HasFactory; // Tambahkan ini jika Anda ingin menggunakan model factory untuk testing/seeding

    protected $table = 'pesanan'; // Nama tabel di database
    protected $primaryKey = 'id'; // Nama primary key tabel
    public $timestamps = true; // Mengaktifkan otomatis created_at dan updated_at oleh Laravel

    protected $fillable = [
        'id_pengguna',
        'total_harga',
        'status',          // Contoh: 'dalam antrian', 'proses pembuatan', 'siap diambil', 'sudah diambil'
        'payment_status',  // Contoh: 'pending', 'paid', 'expired', 'cancelled'
        'payment_type',    // Contoh: 'gopay', 'bank_transfer', 'shopeepay' (Ini adalah kolom yang sebelumnya 'Unknown column')
        'catatan',         // Catatan tambahan dari pelanggan (Ini adalah kolom yang sebelumnya 'doesn't have a default value')
        'midtrans_order_id' // ID transaksi dari Midtrans
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    // Jika Anda menyimpan 'item_details' di model ini atau model lain sebagai JSON,
    // Anda bisa menambahkan casting di sini untuk otomatis konversi ke/dari array/objek PHP.
    // protected $casts = [
    //     'item_details' => 'array',
    // ];


    // --- Relasi Antar Model ---

    /**
     * Get the user that owns the Pesanan.
     */
    public function pengguna()
    {
        // Memastikan nama model 'Pengguna' sudah benar dan foreign key 'id_pengguna' sudah sesuai.
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id'); // 'id' adalah local key di tabel 'pengguna'
    }

    /**
     * Get the details for the Pesanan.
     */
    public function details()
    {
        // Relasi one-to-many ke DetailPesanan.
        // 'id_pesanan' adalah foreign key di tabel 'detail_pesanan' yang merujuk ke 'id' di tabel 'pesanan'.
        return $this->hasMany(DetailPesanan::class, 'id_pesanan', 'id');
    }
}
