<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';

    protected $fillable = [
        'number',
        'date',
        'user_id',
    ];

    // Relasi ke user (Purchase yang membuat transaksi)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke detail pembelian
    public function details()
    {
        return $this->hasMany(PurchaseDetail::class, 'purchase_id');
    }
}