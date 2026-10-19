<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDetail extends Model
{
    use HasFactory;

    protected $table = 'sales_details';

    protected $fillable = [
        'sales_id',
        'inventory_id',
        'qty',
        'price',
    ];

    public function getInventory(): ?Inventory
    {
        return Inventory::withTrashed()->find($this->inventory_id);
    }

    public function getSale(): ?Sale
    {
        return Sale::withTrashed()->find($this->sales_id);
    }
}
