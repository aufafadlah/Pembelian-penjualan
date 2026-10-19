<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    use HasFactory;

    protected $table = 'sales_details';

    protected $fillable = [
        'sales_id',
        'inventory_id',
        'qty',
        'price',
    ];

    // Relasi ke header sales
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sales_id');
    }

    // Relasi ke inventories (barang yang dijual)
    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}