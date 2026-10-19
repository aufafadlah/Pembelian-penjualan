<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $table = 'sales';

    protected $fillable = [
        'number',
        'date',
        'user_id',
    ];

    // Relasi ke user (Sales yang membuat transaksi)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke detail penjualan
    public function details()
    {
        return $this->hasMany(SaleDetail::class, 'sales_id');
    }
}