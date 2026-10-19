<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_details';

    protected $fillable = [
        'purchase_id',
        'inventory_id',
        'qty',
        'price',
    ];

    public function getInventory(): ?Inventory
    {
        return Inventory::withTrashed()->find($this->inventory_id);
    }

    public function getPurchase(): ?Purchase
    {
        return Purchase::withTrashed()->find($this->purchase_id);
    }
}