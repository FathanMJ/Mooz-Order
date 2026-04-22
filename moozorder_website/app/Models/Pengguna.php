<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Disarankan ditambahkan jika ingin menggunakan Factory
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    use Notifiable;
    use HasFactory; // Tambahkan ini jika Anda berencana menggunakan model factories untuk testing/seeding

    protected $table = 'pengguna'; // Nama tabel di database Anda
    protected $primaryKey = 'id'; // Asumsi primary key adalah 'id', ini adalah default Laravel

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nama',
        'email',
        'no_hp',
        'alamat',
        'password',
        'role', // Contoh: 'admin', 'user', 'staff'
        'profile_image_path', // Jika Anda menambahkan kolom untuk menyimpan path gambar profil
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        // Jika Anda memiliki kolom lain yang tidak ingin ditampilkan saat di-serialize ke JSON, tambahkan di sini
        // 'email_verified_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime', // Jika Anda menggunakan fitur verifikasi email Laravel
    ];

    // --- Relasi Antar Model (jika diperlukan) ---

    /**
     * Get the orders for the Pengguna.
     */
    public function pesanan()
    {
        // Relasi one-to-many ke model Pesanan
        // 'id_pengguna' adalah foreign key di tabel 'pesanan'
        return $this->hasMany(Pesanan::class, 'id_pengguna', 'id');
    }

    // Anda bisa menambahkan mutator jika perlu memanipulasi data sebelum disimpan
    // public function setPasswordAttribute($value)
    // {
    //     $this->attributes['password'] = bcrypt($value); // Selalu hash password!
    // }

    // Atau accessor jika perlu format data saat diambil
    // public function getFullNameAttribute()
    // {
    //     return "{$this->first_name} {$this->last_name}";
    // }
}
