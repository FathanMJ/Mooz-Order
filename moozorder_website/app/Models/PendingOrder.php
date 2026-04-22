<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingOrder extends Model
{
    // Tentukan nama tabel jika tidak sesuai konvensi Laravel (jamak dari nama model)
    protected $table = 'pending_orders';

    // Kolom yang dapat diisi secara massal
    protected $fillable = [
        'midtrans_order_id',
        'user_id',
        'item_details',
        'total_amount',
        'catatan',
    ];

    // Atribut yang harus di-cast ke tipe data asli
    protected $casts = [
        'item_details' => 'array',
        'total_amount' => 'decimal:2',
    ];
}
