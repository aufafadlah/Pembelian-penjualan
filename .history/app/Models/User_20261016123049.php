<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'role', // SuperAdmin, Sales, Purchase, Manager
    ];

    // Relasi ke tabel sales
    public function sales()
    {
        return $this->hasMany(Sale::class, 'user_id');
    }

    // Relasi ke tabel purchases
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'user_id');
    }
}