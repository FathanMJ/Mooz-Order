<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailPesanan extends Model
{
    use HasFactory;

    protected $table = 'detail_pesanan';
    protected $primaryKey = 'id';
    public $timestamps = false; // Jika tabel ini tidak menggunakan created_at dan updated_at

    protected $fillable = [
        'id_pesanan',
        'id_produk',     // Foreign key yang menyimpan ID produk (misal: "P0000001")
        'jumlah',
        'harga_satuan',
        'subtotal',
        'ukuran_produk', // <--- INI BENAR: Sesuai dengan nama kolom di DB Anda
        'catatan'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'jumlah' => 'integer',
        'harga_satuan' => 'float',
        'subtotal' => 'float',
    ];

    // --- Relasi Antar Model ---

    /**
     * Get the pesanan that owns the DetailPesanan.
     */
    public function pesanan()
    {
        // Relasi many-to-one ke model Pesanan
        // 'id_pesanan' adalah foreign key di tabel 'detail_pesanan' yang merujuk ke 'id' di tabel 'pesanan'.
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id');
    }

    /**
     * Get the product associated with the DetailPesanan.
     */
    public function product() // Nama metode ini harus 'product' agar `->with('details.product')` berfungsi
    {
        // PERBAIKAN KRITIS UNTUK 'Unknown Product':
        // Ini adalah relasi yang sangat penting.
        // - 'id_produk' (argumen kedua): adalah nama foreign key di tabel `detail_pesanan`.
        // - 'id_produk' (argumen ketiga): adalah nama kolom di tabel `produk` yang dicocokkan dengan `detail_pesanan.id_produk`.
        //   Kita secara eksplisit memberitahu Laravel untuk mencocokkan dengan kolom `id_produk` di tabel `produk`,
        //   BUKAN primary key default `id` dari model `Produk`.
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}
