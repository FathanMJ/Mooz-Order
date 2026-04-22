<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class M_laporankeuangan extends Model
{
    protected $table = 'laporan_keuangan';

    public $timestamps = false;

    protected $fillable = ['tanggal', 'keterangan', 'pemasukan', 'pengeluaran'];
}