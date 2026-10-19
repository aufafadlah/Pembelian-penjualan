<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';

    protected $fillable = [
        'code',
        'name',
        'price',
        'stock',
    ];

    // Relasi ke sale_details
    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class, 'inventory_id');
    }

    // Relasi ke purchase_details
    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class, 'inventory_id');
    }
}