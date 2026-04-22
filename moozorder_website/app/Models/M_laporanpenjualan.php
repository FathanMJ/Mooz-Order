<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_laporanpenjualan extends Model
{
    protected $table = 'laporan_penjualan'; // Nama tabel di database
    protected $primaryKey = 'id';           // Primary key tabel
    public $timestamps = false;             // Tidak menggunakan kolom created_at dan updated_at

    protected $fillable = [
        'id_pesanan',
        'id_produk',
        'jumlah',
        'harga_satuan',
        'total_harga',
        'waktu_diambil',
        'id_pengguna' // Pastikan kolom ini ada di tabel laporan_penjualan
    ];

    // Relasi dengan model M_produk
    public function produk()
    {
        return $this->belongsTo(M_produk::class, 'id_produk', 'id_produk');
    }

    // Relasi dengan model M_kelolapesanan (opsional, karena data pesanan akan dihapus)
    public function pesanan()
    {
        return $this->belongsTo(M_kelolapesanan::class, 'id_pesanan', 'id');
    }

    // Relasi dengan model M_pengguna
    public function pengguna()
    {
        return $this->belongsTo(M_pengguna::class, 'id_pengguna', 'id'); // Sesuaikan 'id' dengan primary key tabel pengguna Anda
    }
}