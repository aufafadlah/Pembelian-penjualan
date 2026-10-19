<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales';

    protected $fillable = [
        'number',
        'date',
        'user_id',
    ];

    public function details()
    {
        return $this->hasMany(SalesDetail::class, 'sales_id');
    }

    public function getUser(): ?User
    {
        return User::find($this->user_id);
    }

    public static function generateNumber(): string
    {
        $today_prefix = 'SO-' . date('Ymd') . '-';
        $latest_sale = self::withTrashed()
            ->where('number', 'like', $today_prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $sequence_number = 1;
        if ($latest_sale && preg_match('/-(\d+)$/', $latest_sale->number, $matches)) {
            $sequence_number = (int) $matches[1] + 1;
        }

        return $today_prefix . str_pad((string)$sequence_number, 4, '0', STR_PAD_LEFT);
    }

    public static function storeSale(array $data, int $user_id): array
    {
        if (empty($data['items']) || !is_array($data['items'])) {
            return [
                'status' => false,
                'message' => 'At least one sales item must be added.',
            ];
        }

        foreach ($data['items'] as $item) {
            $inventory = Inventory::find((int) $item['inventory_id']);
            if (!$inventory) {
                return [
                    'status' => false,
                    'message' => 'Inventory item not found.',
                ];
            }

            $requested_qty = (int) $item['qty'];
            if ($inventory->stock < $requested_qty) {
                return [
                    'status' => false,
                    'message' => "Insufficient stock for {$inventory->name}. Available: {$inventory->stock}, Requested: {$requested_qty}.",
                ];
            }
        }

        return DB::transaction(function () use ($data, $user_id) {
            $sale_number = !empty($data['number']) ? $data['number'] : self::generateNumber();

            $sale = self::create([
                'number' => $sale_number,
                'date' => $data['date'] ?? date('Y-m-d'),
                'user_id' => $user_id,
            ]);

            foreach ($data['items'] as $item) {
                $item_inventory_id = (int) $item['inventory_id'];
                $item_qty = (int) $item['qty'];
                $item_price = (float) $item['price'];

                SalesDetail::create([
                    'sales_id' => $sale->id,
                    'inventory_id' => $item_inventory_id,
                    'qty' => $item_qty,
                    'price' => $item_price,
                ]);

                $inventory = Inventory::find($item_inventory_id);
                if ($inventory) {
                    $inventory->decrement('stock', $item_qty);
                }
            }

            return [
                'status' => true,
                'message' => 'Sales transaction created successfully.',
                'sale' => $sale,
            ];
        });
    }

    public static function updateSale(int $sale_id, array $data): array
    {
        $sale = self::find($sale_id);
        if (!$sale) {
            return [
                'status' => false,
                'message' => 'Sale transaction not found.',
            ];
        }

        if (empty($data['items']) || !is_array($data['items'])) {
            return [
                'status' => false,
                'message' => 'At least one sales item must be added.',
            ];
        }

        DB::beginTransaction();

        $old_details = SalesDetail::where('sales_id', $sale->id)->get();
        foreach ($old_details as $old_detail) {
            $inventory = Inventory::find($old_detail->inventory_id);
            if ($inventory) {
                $inventory->increment('stock', (int) $old_detail->qty);
            }
        }

        foreach ($data['items'] as $item) {
            $inventory = Inventory::find((int) $item['inventory_id']);
            if (!$inventory) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => 'Inventory item not found.',
                ];
            }

            $requested_qty = (int) $item['qty'];
            if ($inventory->stock < $requested_qty) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => "Insufficient stock for {$inventory->name}. Available: {$inventory->stock}, Requested: {$requested_qty}.",
                ];
            }
        }

        SalesDetail::where('sales_id', $sale->id)->delete();

        foreach ($data['items'] as $item) {
            $item_inventory_id = (int) $item['inventory_id'];
            $item_qty = (int) $item['qty'];
            $item_price = (float) $item['price'];

            SalesDetail::create([
                'sales_id' => $sale->id,
                'inventory_id' => $item_inventory_id,
                'qty' => $item_qty,
                'price' => $item_price,
            ]);

            $inventory = Inventory::find($item_inventory_id);
            if ($inventory) {
                $inventory->decrement('stock', $item_qty);
            }
        }

        $sale->update([
            'number' => $data['number'] ?? $sale->number,
            'date' => $data['date'] ?? $sale->date,
        ]);

        DB::commit();

        return [
            'status' => true,
            'message' => 'Sales transaction updated successfully.',
            'sale' => $sale,
        ];
    }

    public static function deleteSale(int $sale_id): bool
    {
        return DB::transaction(function () use ($sale_id) {
            $sale = self::find($sale_id);
            if (!$sale) {
                return false;
            }

            $details = SalesDetail::where('sales_id', $sale->id)->get();
            foreach ($details as $detail) {
                $inventory = Inventory::find($detail->inventory_id);
                if ($inventory) {
                    $inventory->increment('stock', (int) $detail->qty);
                }
            }

            return (bool) $sale->delete();
        });
    }

    public static function getDataTable($request, int $user_id, string $user_role): array
    {
        $base_query = self::query();
        if ($user_role === 'Sales') {
            $base_query->where('user_id', $user_id);
        }

        $totalData = $base_query->count();

        $query = self::query();
        if ($user_role === 'Sales') {
            $query->where('user_id', $user_id);
        }

        $search_value = $request->input('search.value');
        if (!empty($search_value)) {
            $matching_user_ids = User::where('name', 'ilike', "%{$search_value}%")->pluck('id')->toArray();
            $query->where(function ($sub_query) use ($search_value, $matching_user_ids) {
                $sub_query->where('number', 'ilike', "%{$search_value}%");
                if (!empty($matching_user_ids)) {
                    $sub_query->orWhereIn('user_id', $matching_user_ids);
                }
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
        $columns = ['id', 'number', 'date', 'user_id', 'id', 'id'];

        if (isset($columns[$order_column_index])) {
            $query->orderBy($columns[$order_column_index], $order_direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        $sales = $query->with('details')->get();

        $formatted_data = [];
        $row_number = $start + 1;

        foreach ($sales as $sale) {
            $user = $sale->getUser();
            $item_count = $sale->details->count();
            $total_amount = $sale->details->sum(function ($detail) {
                return (int) $detail->qty * (float) $detail->price;
            });

            $formatted_data[] = [
                'row_number' => $row_number++,
                'id' => $sale->id,
                'number' => $sale->number,
                'date' => $sale->date,
                'user_name' => $user ? $user->name : '-',
                'item_count' => $item_count,
                'total_amount' => $total_amount,
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
