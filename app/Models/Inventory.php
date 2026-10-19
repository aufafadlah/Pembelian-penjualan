<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventories';

    protected $fillable = [
        'code',
        'name',
        'price',
        'stock',
    ];

    public function salesDetails()
    {
        return $this->hasMany(SalesDetail::class, 'inventory_id');
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class, 'inventory_id');
    }

    public static function generateCode(): string
    {
        $latest_item = self::withTrashed()->orderBy('id', 'desc')->first();
        $next_number = $latest_item ? ($latest_item->id + 1) : 1;
        return 'BRG-' . str_pad((string)$next_number, 4, '0', STR_PAD_LEFT);
    }

    public static function storeInventory(array $data): self
    {
        if (empty($data['code'])) {
            $data['code'] = self::generateCode();
        }

        return self::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'price' => $data['price'],
            'stock' => $data['stock'] ?? 0,
        ]);
    }

    public static function updateInventory(int $inventory_id, array $data): ?self
    {
        $inventory = self::find($inventory_id);
        if (!$inventory) {
            return null;
        }

        $inventory->update([
            'code' => $data['code'] ?? $inventory->code,
            'name' => $data['name'],
            'price' => $data['price'],
            'stock' => $data['stock'] ?? $inventory->stock,
        ]);

        return $inventory;
    }

    public static function deleteInventory(int $inventory_id): bool
    {
        $inventory = self::find($inventory_id);
        if (!$inventory) {
            return false;
        }

        return (bool) $inventory->delete();
    }

    public static function getSelectOptions()
    {
        return self::orderBy('name', 'asc')
            ->select('id', 'code', 'name', 'price', 'stock')
            ->get();
    }

    public static function getDataTable($request): array
    {
        $totalData = self::count();

        $query = self::query();

        $search_value = $request->input('search.value');
        if (!empty($search_value)) {
            $query->where(function ($sub_query) use ($search_value) {
                $sub_query->where('name', 'ilike', "%{$search_value}%")
                    ->orWhere('code', 'ilike', "%{$search_value}%");
            });
        }

        $totalFiltered = $query->count();

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $order_column_index = $request->input('order.0.column');
        $order_direction = $request->input('order.0.dir', 'desc');
        $columns = ['id', 'code', 'name', 'price', 'stock', 'id'];

        if (isset($columns[$order_column_index])) {
            $query->orderBy($columns[$order_column_index], $order_direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        $items = $query->get();

        $formatted_data = [];
        $row_number = $start + 1;

        foreach ($items as $item) {
            $formatted_data[] = [
                'row_number' => $row_number++,
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'price' => (float) $item->price,
                'stock' => (int) $item->stock,
            ];
        }

        return [
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'totalData' => $totalData,
            'totalFiltered' => $totalFiltered,
            'data' => $formatted_data,
        ];
    }
}