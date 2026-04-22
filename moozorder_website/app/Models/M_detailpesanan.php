<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_detailpesanan extends Model
{
    protected $table = 'detail_pesanan';

    public $timestamps = false;

    protected $fillable = ['id_pesanan', 'id_produk', 'jumlah', 'harga_satuan'];

    public function produk()
    {
        return $this->belongsTo(M_produk::class, 'id_produk', 'id_produk');
    }
}
