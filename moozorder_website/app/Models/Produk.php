<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $primaryKey = 'id'; // <--- INI BENAR: Primary Key Anda adalah kolom 'id' (integer auto-increment)
    public $incrementing = true; // <--- INI BENAR: Karena 'id' adalah auto_increment
    protected $keyType = 'int'; // <--- INI BENAR: Tipe data untuk primary key adalah integer

    protected $fillable = [
        // 'id_produk', // 'id_produk' tidak perlu di fillable jika Anda tidak mengaturnya secara mass assignment saat membuat produk
                         // (misalnya jika Anda mengisinya secara manual atau di logic controller)
        'id_kategori',
        'kategori_produk',
        'nama_produk',       // <--- Kolom yang menyimpan nama produk
        'keterangan_produk',
        'ukuran_produk',     // Ini adalah kolom enum 'kecil','besar','sedang' di tabel 'produk'
                             // Jika ini adalah ukuran standar produk dan bukan ukuran spesifik yang dipesan,
                             // maka keberadaannya di sini benar. Ukuran yang dipesan disimpan di detail_pesanan.
        'harga_produk',
        'foto_produk'
        // 'created_at' dan 'updated_at' tidak perlu di fillable jika timestamps diaktifkan (default true)
    ];

        public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id');
    }
    // Jika Anda memiliki kolom 'created_at' dan 'updated_at' di tabel 'produk',
    // pastikan tidak ada $timestamps = false; di model ini. Default-nya adalah true.
}
