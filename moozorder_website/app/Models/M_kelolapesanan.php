<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\M_pengguna;      // Penting: Impor model M_pengguna
use App\Models\M_detailpesanan; // Penting: Impor model M_detailpesanan

class M_kelolapesanan extends Model
{
    // Nama tabel di database Anda. Pastikan ini sesuai.
    // Berdasarkan skema dan diskusi, kita asumsikan 'pesanan'.
    protected $table = 'pesanan';

    // Kolom-kolom yang diperbolehkan untuk pengisian massal.
    protected $fillable = [
        'id_pengguna',
        'status',
        'catatan',
        // Tambahkan kolom lain di sini jika ada, seperti 'total_harga' atau 'midtrans_order_id'
        // jika Anda ingin mengisinya secara massal.
        // Contoh: 'total_harga', 'midtrans_order_id', 'payment_status'
    ];

    // Secara default, Laravel akan mengasumsikan primary key adalah 'id'
    // dan timestamps (created_at, updated_at) aktif.
    // Jika tidak demikian, Anda bisa menentukannya secara eksplisit:
    // protected $primaryKey = 'id';
    // public $timestamps = true; // atau false

    /**
     * Relasi one-to-many: Sebuah pesanan memiliki banyak detail pesanan.
     */
    public function details()
    {
        // 'id_pesanan' adalah foreign key di tabel 'detail_pesanan' yang merujuk ke 'id' di tabel 'pesanan'.
        return $this->hasMany(M_detailpesanan::class, 'id_pesanan');
    }

    /**
     * Relasi belongs-to: Sebuah pesanan dimiliki oleh seorang pengguna.
     * Nama metode 'pengguna' cocok dengan pemanggilan di controller/view.
     */
    public function pengguna()
    {
        // Parameter 1: Model terkait (M_pengguna).
        // Parameter 2: Foreign key di tabel 'pesanan' (id_pengguna).
        // Parameter 3: Primary key di tabel 'pengguna' (id).
        return $this->belongsTo(M_pengguna::class, 'id_pengguna', 'id');
    }
}
