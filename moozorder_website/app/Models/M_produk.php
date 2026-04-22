<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_produk extends Model
{
    use HasFactory;

    protected $table = 'produk';

    protected $fillable = [
        'id_produk',
        'id_kategori',
        'kategori_produk',
        'nama_produk',
        'ukuran_produk',
        'keterangan_produk',
        'harga_produk',
        'foto_produk'
    ];

    protected $casts = [
        'foto_produk' => 'array',
        'id_kategori' => 'integer'
    ];

    public static function generateIdProduk()
    {
        $lastProduct = self::orderBy('id', 'desc')->first();
        if (!$lastProduct) {
            return 'P0000001';
        }
        $lastId = substr($lastProduct->id_produk, 1);
        $nextId = 'P' . str_pad((int)$lastId + 1, 7, '0', STR_PAD_LEFT);
        return $nextId;
    }

    // Accessor untuk foto_produk
    public function getFotoArrayAttribute()
    {
        return json_decode($this->foto_produk) ?? [];
    }

        public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id');
    }
}
