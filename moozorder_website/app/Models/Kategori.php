<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kategori extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kategori'; // Nama tabel di database adalah 'kategoris' (plural)

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id'; // Laravel secara default menganggap primary key adalah 'id', jadi ini opsional tapi baik untuk eksplisit.

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_kategori'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true; // Laravel secara default menganggap tabel memiliki created_at dan updated_at, jadi ini opsional tapi baik untuk eksplisit.

    /**
     * Get the produk associated with the kategori.
     * Mendefinisikan relasi: Satu Kategori memiliki banyak Produk.
     */
    public function produk()
    {
        return $this->hasMany(Produk::class, 'id_kategori'); // Sesuaikan 'Produk::class' jika model produk Anda memiliki namespace berbeda
    }

    // Accessor untuk memformat nama kategori
    public function getNamaKategoriFormattedAttribute()
    {
        return ucwords(strtolower($this->nama_kategori));
    }

    // Accessor untuk memformat deskripsi
    public function getDeskripsiFormattedAttribute()
    {
        return $this->deskripsi ? ucfirst($this->deskripsi) : '-';
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where('nama_kategori', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
    }
}
