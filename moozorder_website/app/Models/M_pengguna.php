<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Auth\Passwords\CanResetPassword; // <--- Tambahkan ini

class M_pengguna extends Authenticatable implements JWTSubject
{
    use HasFactory, CanResetPassword; // <--- Gunakan trait ini

    protected $table = 'pengguna';

    protected $fillable = [
        'nama', 'email', 'no_hp', 'alamat', 'password', 'role', 'remember_token'
    ];

    protected $casts = [
        'password' => 'string',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
