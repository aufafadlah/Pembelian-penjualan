<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * ModelExtensions defines application domain models inheriting from Laracademy generated classes
 * without modifying any files in app/Models/
 */

class Inventory extends \App\Models\Inventories
{
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

    public static function storeInventory(array $data)
    {
        if (empty($data['code'])) {
            $latest_item = self::withTrashed()->orderBy('id', 'desc')->first();
            $next_number = $latest_item ? ($latest_item->id + 1) : 1;
            $data['code'] = 'BRG-' . str_pad((string) $next_number, 4, '0', STR_PAD_LEFT);
        }

        return self::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'price' => $data['price'],
            'stock' => $data['stock'] ?? 0,
        ]);
    }

    public static function updateInventory(int $inventory_id, array $data)
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
}

class SaleDetail extends \App\Models\SalesDetails
{
    protected $table = 'sales_details';
    public $timestamps = false;

    public function getInventory()
    {
        return Inventory::find($this->inventory_id);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sales_id', 'id');
    }
}

class PurchaseDetail extends \App\Models\PurchaseDetails
{
    protected $table = 'purchase_details';
    public $timestamps = false;

    public function getInventory()
    {
        return Inventory::find($this->inventory_id);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }
}

class Sale extends \App\Models\Sales
{
    protected $table = 'sales';

    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class, 'sales_id', 'id');
    }

    public function getUser()
    {
        return \App\Models\Users::find($this->user_id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users::class, 'user_id', 'id');
    }

    public static function getDataTable($request, int $userId, string $userRole): array
    {
        $query = self::query()->whereNull('deleted_at');

        if ($userRole === 'Sales') {
            $query->where('user_id', $userId);
        }

        $totalData = (clone $query)->count();

        $search_value = $request->input('search.value');
        if (!empty($search_value)) {
            $query->where(function ($sub) use ($search_value) {
                $sub->where('number', 'ilike', "%{$search_value}%")
                    ->orWhere('date', 'ilike', "%{$search_value}%");
            });
        }

        $totalFiltered = (clone $query)->count();

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $query->orderBy('id', 'desc');
        $sales = $query->with('details')->get();

        $formatted_data = [];
        $row_number = $start + 1;

        foreach ($sales as $sale) {
            $totalAmount = 0;
            $itemCount = 0;

            foreach ($sale->details as $d) {
                $totalAmount += ((int) $d->qty * (float) $d->price);
                $itemCount += (int) $d->qty;
            }

            $user = $sale->getUser();

            $formatted_data[] = [
                'row_number' => $row_number++,
                'id' => $sale->id,
                'number' => $sale->number,
                'date' => $sale->date ? Carbon::parse($sale->date)->format('Y-m-d') : '-',
                'user_name' => $user ? $user->name : '-',
                'item_count' => $itemCount,
                'total_amount' => $totalAmount,
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

    public static function storeSale(array $data, int $userId): array
    {
        return DB::transaction(function () use ($data, $userId) {
            // Validate stock for all items first
            foreach ($data['items'] as $item) {
                $inv = Inventory::lockForUpdate()->find($item['inventory_id']);
                if (!$inv) {
                    return [
                        'status' => false,
                        'message' => "Inventory item #{$item['inventory_id']} does not exist.",
                    ];
                }
                if ($inv->stock < $item['qty']) {
                    return [
                        'status' => false,
                        'message' => "Insufficient stock for {$inv->name}. Available: {$inv->stock}, requested: {$item['qty']}.",
                    ];
                }
            }

            $invoiceNumber = $data['number'] ?? null;
            if (empty($invoiceNumber)) {
                $latest = self::withTrashed()->orderBy('id', 'desc')->first();
                $nextId = $latest ? ($latest->id + 1) : 1;
                $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
            }

            $sale = self::create([
                'number' => $invoiceNumber,
                'date' => $data['date'],
                'user_id' => $userId,
            ]);

            foreach ($data['items'] as $item) {
                SaleDetail::create([
                    'sales_id' => $sale->id,
                    'inventory_id' => $item['inventory_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                ]);

                Inventory::where('id', $item['inventory_id'])->decrement('stock', (int) $item['qty']);
            }

            return [
                'status' => true,
                'message' => 'Sale transaction created successfully.',
                'sale' => $sale->load('details'),
            ];
        });
    }

    public static function updateSale(int $id, array $data): array
    {
        return DB::transaction(function () use ($id, $data) {
            $sale = self::find($id);
            if (!$sale) {
                return [
                    'status' => false,
                    'message' => 'Sale transaction not found.',
                ];
            }

            // Restore previous stock
            $oldDetails = SaleDetail::where('sales_id', $id)->get();
            foreach ($oldDetails as $old) {
                Inventory::where('id', $old->inventory_id)->increment('stock', (int) $old->qty);
            }

            // Check new stock requirements
            foreach ($data['items'] as $item) {
                $inv = Inventory::lockForUpdate()->find($item['inventory_id']);
                if (!$inv) {
                    return [
                        'status' => false,
                        'message' => "Inventory item #{$item['inventory_id']} does not exist.",
                    ];
                }
                if ($inv->stock < $item['qty']) {
                    return [
                        'status' => false,
                        'message' => "Insufficient stock for {$inv->name}. Available: {$inv->stock}, requested: {$item['qty']}.",
                    ];
                }
            }

            // Remove old details
            SaleDetail::where('sales_id', $id)->delete();

            // Update sale record
            $sale->update([
                'number' => $data['number'] ?? $sale->number,
                'date' => $data['date'],
            ]);

            // Insert new details & decrement stock
            foreach ($data['items'] as $item) {
                SaleDetail::create([
                    'sales_id' => $sale->id,
                    'inventory_id' => $item['inventory_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                ]);

                Inventory::where('id', $item['inventory_id'])->decrement('stock', (int) $item['qty']);
            }

            return [
                'status' => true,
                'message' => 'Sale transaction updated successfully.',
                'sale' => $sale->load('details'),
            ];
        });
    }

    public static function deleteSale(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $sale = self::find($id);
            if (!$sale) {
                return false;
            }

            // Restore stock
            $details = SaleDetail::where('sales_id', $id)->get();
            foreach ($details as $detail) {
                Inventory::where('id', $detail->inventory_id)->increment('stock', (int) $detail->qty);
            }

            SaleDetail::where('sales_id', $id)->delete();
            $sale->delete();

            return true;
        });
    }

    public static function getSalesCount(string $role, int $userId): int
    {
        $q = self::query()->whereNull('deleted_at');
        if ($role === 'Sales') {
            $q->where('user_id', $userId);
        }
        return $q->count();
    }

    public static function getSalesAmount(string $role, int $userId): float
    {
        $q = self::query()->whereNull('deleted_at');
        if ($role === 'Sales') {
            $q->where('user_id', $userId);
        }
        $saleIds = $q->pluck('id');

        return (float) (DB::table('sales_details')
            ->whereIn('sales_id', $saleIds)
            ->selectRaw('SUM(qty * price) as total')
            ->value('total') ?? 0);
    }

    public static function getRecentSales(string $role, int $userId, int $limit = 5)
    {
        $q = self::query()->whereNull('deleted_at');
        if ($role === 'Sales') {
            $q->where('user_id', $userId);
        }
        return $q->orderBy('id', 'desc')->take($limit)->with('details')->get();
    }
}

class Purchase extends \App\Models\Purchases
{
    protected $table = 'purchases';

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class, 'purchase_id', 'id');
    }

    public function getUser()
    {
        return \App\Models\Users::find($this->user_id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users::class, 'user_id', 'id');
    }

    public static function getDataTable($request, int $userId, string $userRole): array
    {
        $query = self::query()->whereNull('deleted_at');

        if ($userRole === 'Purchase') {
            $query->where('user_id', $userId);
        }

        $totalData = (clone $query)->count();

        $search_value = $request->input('search.value');
        if (!empty($search_value)) {
            $query->where(function ($sub) use ($search_value) {
                $sub->where('number', 'ilike', "%{$search_value}%")
                    ->orWhere('date', 'ilike', "%{$search_value}%");
            });
        }

        $totalFiltered = (clone $query)->count();

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $query->orderBy('id', 'desc');
        $purchases = $query->with('details')->get();

        $formatted_data = [];
        $row_number = $start + 1;

        foreach ($purchases as $p) {
            $totalAmount = 0;
            $itemCount = 0;

            foreach ($p->details as $d) {
                $totalAmount += ((int) $d->qty * (float) $d->price);
                $itemCount += (int) $d->qty;
            }

            $user = $p->getUser();

            $formatted_data[] = [
                'row_number' => $row_number++,
                'id' => $p->id,
                'number' => $p->number,
                'date' => $p->date ? Carbon::parse($p->date)->format('Y-m-d') : '-',
                'user_name' => $user ? $user->name : '-',
                'item_count' => $itemCount,
                'total_amount' => $totalAmount,
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

    public static function storePurchase(array $data, int $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $poNumber = $data['number'] ?? null;
            if (empty($poNumber)) {
                $latest = self::withTrashed()->orderBy('id', 'desc')->first();
                $nextId = $latest ? ($latest->id + 1) : 1;
                $poNumber = 'PO-' . date('Ymd') . '-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
            }

            $purchase = self::create([
                'number' => $poNumber,
                'date' => $data['date'],
                'user_id' => $userId,
            ]);

            foreach ($data['items'] as $item) {
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'inventory_id' => $item['inventory_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                ]);

                Inventory::where('id', $item['inventory_id'])->increment('stock', (int) $item['qty']);
            }

            return $purchase->load('details');
        });
    }

    public static function updatePurchase(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $purchase = self::find($id);
            if (!$purchase) {
                return null;
            }

            // Revert previous stock addition
            $oldDetails = PurchaseDetail::where('purchase_id', $id)->get();
            foreach ($oldDetails as $old) {
                Inventory::where('id', $old->inventory_id)->decrement('stock', (int) $old->qty);
            }

            PurchaseDetail::where('purchase_id', $id)->delete();

            $purchase->update([
                'number' => $data['number'] ?? $purchase->number,
                'date' => $data['date'],
            ]);

            foreach ($data['items'] as $item) {
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'inventory_id' => $item['inventory_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                ]);

                Inventory::where('id', $item['inventory_id'])->increment('stock', (int) $item['qty']);
            }

            return $purchase->load('details');
        });
    }

    public static function deletePurchase(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $purchase = self::find($id);
            if (!$purchase) {
                return false;
            }

            // Revert stock added by this purchase
            $details = PurchaseDetail::where('purchase_id', $id)->get();
            foreach ($details as $detail) {
                Inventory::where('id', $detail->inventory_id)->decrement('stock', (int) $detail->qty);
            }

            PurchaseDetail::where('purchase_id', $id)->delete();
            $purchase->delete();

            return true;
        });
    }

    public static function getPurchasesCount(string $role, int $userId): int
    {
        $q = self::query()->whereNull('deleted_at');
        if ($role === 'Purchase') {
            $q->where('user_id', $userId);
        }
        return $q->count();
    }

    public static function getPurchasesAmount(string $role, int $userId): float
    {
        $q = self::query()->whereNull('deleted_at');
        if ($role === 'Purchase') {
            $q->where('user_id', $userId);
        }
        $purchaseIds = $q->pluck('id');

        return (float) (DB::table('purchase_details')
            ->whereIn('purchase_id', $purchaseIds)
            ->selectRaw('SUM(qty * price) as total')
            ->value('total') ?? 0);
    }

    public static function getRecentPurchases(string $role, int $userId, int $limit = 5)
    {
        $q = self::query()->whereNull('deleted_at');
        if ($role === 'Purchase') {
            $q->where('user_id', $userId);
        }
        return $q->orderBy('id', 'desc')->take($limit)->with('details')->get();
    }
}
