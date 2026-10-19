<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchases';

    protected $fillable = [
        'number',
        'date',
        'user_id',
    ];

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class, 'purchase_id');
    }

    public function getUser(): ?User
    {
        return User::find($this->user_id);
    }

    public static function generateNumber(): string
    {
        $today_prefix = 'PO-' . date('Ymd') . '-';
        $latest_purchase = self::withTrashed()
            ->where('number', 'like', $today_prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $sequence_number = 1;
        if ($latest_purchase && preg_match('/-(\d+)$/', $latest_purchase->number, $matches)) {
            $sequence_number = (int) $matches[1] + 1;
        }

        return $today_prefix . str_pad((string)$sequence_number, 4, '0', STR_PAD_LEFT);
    }

    public static function storePurchase(array $data, int $user_id): self
    {
        return DB::transaction(function () use ($data, $user_id) {
            $purchase_number = !empty($data['number']) ? $data['number'] : self::generateNumber();

            $purchase = self::create([
                'number' => $purchase_number,
                'date' => $data['date'] ?? date('Y-m-d'),
                'user_id' => $user_id,
            ]);

            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $item_inventory_id = (int) $item['inventory_id'];
                    $item_qty = (int) $item['qty'];
                    $item_price = (float) $item['price'];

                    PurchaseDetail::create([
                        'purchase_id' => $purchase->id,
                        'inventory_id' => $item_inventory_id,
                        'qty' => $item_qty,
                        'price' => $item_price,
                    ]);

                    $inventory = Inventory::find($item_inventory_id);
                    if ($inventory) {
                        $inventory->increment('stock', $item_qty);
                    }
                }
            }

            return $purchase;
        });
    }

    public static function updatePurchase(int $purchase_id, array $data): ?self
    {
        return DB::transaction(function () use ($purchase_id, $data) {
            $purchase = self::find($purchase_id);
            if (!$purchase) {
                return null;
            }

            $old_details = PurchaseDetail::where('purchase_id', $purchase->id)->get();
            foreach ($old_details as $old_detail) {
                $inventory = Inventory::find($old_detail->inventory_id);
                if ($inventory) {
                    $inventory->decrement('stock', (int) $old_detail->qty);
                }
            }

            PurchaseDetail::where('purchase_id', $purchase->id)->delete();

            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $item_inventory_id = (int) $item['inventory_id'];
                    $item_qty = (int) $item['qty'];
                    $item_price = (float) $item['price'];

                    PurchaseDetail::create([
                        'purchase_id' => $purchase->id,
                        'inventory_id' => $item_inventory_id,
                        'qty' => $item_qty,
                        'price' => $item_price,
                    ]);

                    $inventory = Inventory::find($item_inventory_id);
                    if ($inventory) {
                        $inventory->increment('stock', $item_qty);
                    }
                }
            }

            $purchase->update([
                'number' => $data['number'] ?? $purchase->number,
                'date' => $data['date'] ?? $purchase->date,
            ]);

            return $purchase;
        });
    }

    public static function deletePurchase(int $purchase_id): bool
    {
        return DB::transaction(function () use ($purchase_id) {
            $purchase = self::find($purchase_id);
            if (!$purchase) {
                return false;
            }

            $details = PurchaseDetail::where('purchase_id', $purchase->id)->get();
            foreach ($details as $detail) {
                $inventory = Inventory::find($detail->inventory_id);
                if ($inventory) {
                    $inventory->decrement('stock', (int) $detail->qty);
                }
            }

            return (bool) $purchase->delete();
        });
    }

    public static function getDataTable($request, int $user_id, string $user_role): array
    {
        $base_query = self::query();
        if ($user_role === 'Purchase') {
            $base_query->where('user_id', $user_id);
        }

        $totalData = $base_query->count();

        $query = self::query();
        if ($user_role === 'Purchase') {
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

        $purchases = $query->with('details')->get();

        $formatted_data = [];
        $row_number = $start + 1;

        foreach ($purchases as $purchase) {
            $user = $purchase->getUser();
            $item_count = $purchase->details->count();
            $total_amount = $purchase->details->sum(function ($detail) {
                return (int) $detail->qty * (float) $detail->price;
            });

            $formatted_data[] = [
                'row_number' => $row_number++,
                'id' => $purchase->id,
                'number' => $purchase->number,
                'date' => $purchase->date,
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