<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Purchases;
use App\Models\PurchaseDetails;
use App\Models\Inventories;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function data(Request $request)
    {
        $query = Purchases::query()->whereNull('deleted_at');

        $totalData = (clone $query)->count();

        $search_value = $request->input('search.value');
        if (!empty($search_value)) {
            $query->where(function ($sub) use ($search_value) {
                $sub->where('number', 'ilike', "%{$search_value}%")
                    ->orWhere('date', 'ilike', "%{$search_value}%");
            });
        }

        $totalFiltered = (clone $query)->count();

        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        if ($length > 0) {
            $query->skip($start)->take($length);
        }
        $query->orderBy('id', 'desc');
        $purchases = $query->get();

        $formatted_data = [];
        $row_number = $start + 1;

        foreach ($purchases as $purchase) {
            $details      = PurchaseDetails::where('purchase_id', $purchase->id)->get();
            $total_amount = 0;
            $item_count   = 0;
            foreach ($details as $d) {
                $total_amount += ((int) $d->qty * (float) $d->price);
                $item_count   += (int) $d->qty;
            }
            $user = Users::find($purchase->user_id);

            $formatted_data[] = [
                'row_number'   => $row_number++,
                'id'           => $purchase->id,
                'number'       => $purchase->number,
                'date'         => $purchase->date ? Carbon::parse($purchase->date)->format('Y-m-d') : '-',
                'user_name'    => $user ? $user->name : '-',
                'item_count'   => $item_count,
                'total_amount' => $total_amount,
            ];
        }

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data'            => $formatted_data,
        ]);
    }

    public function show(int $id)
    {
        $purchase = Purchases::find($id);
        if (!$purchase) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Purchase transaction not found.',
            ], 404);
        }

        $details         = PurchaseDetails::where('purchase_id', $id)->get();
        $formatted_items = [];
        foreach ($details as $detail) {
            $inventory         = Inventories::find($detail->inventory_id);
            $formatted_items[] = [
                'id'             => $detail->id,
                'inventory_id'   => $detail->inventory_id,
                'inventory_name' => $inventory ? $inventory->name : '-',
                'inventory_code' => $inventory ? $inventory->code : '-',
                'qty'            => (int) $detail->qty,
                'price'          => (float) $detail->price,
                'subtotal'       => (int) $detail->qty * (float) $detail->price,
            ];
        }

        $user = Users::find($purchase->user_id);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'        => $purchase->id,
                'number'    => $purchase->number,
                'date'      => $purchase->date,
                'user_name' => $user ? $user->name : '-',
                'items'     => $formatted_items,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number'               => 'nullable|string|max:50',
            'date'                 => 'required|date',
            'items'                => 'required|array|min:1',
            'items.*.inventory_id' => 'required|integer',
            'items.*.qty'          => 'required|integer|min:1',
            'items.*.price'        => 'required|numeric|min:0',
        ]);

        $user_id = (int) session('user_id');

        return DB::transaction(function () use ($validated, $user_id) {
            $po_number = $validated['number'] ?? null;
            if (empty($po_number)) {
                $latest    = Purchases::withTrashed()->orderBy('id', 'desc')->first();
                $next_id   = $latest ? ($latest->id + 1) : 1;
                $po_number = 'PO-' . date('Ymd') . '-' . str_pad((string) $next_id, 4, '0', STR_PAD_LEFT);
            }

            $purchase = Purchases::create([
                'number'  => $po_number,
                'date'    => $validated['date'],
                'user_id' => $user_id,
            ]);

            foreach ($validated['items'] as $item) {
                PurchaseDetails::create([
                    'purchase_id'  => $purchase->id,
                    'inventory_id' => $item['inventory_id'],
                    'qty'          => $item['qty'],
                    'price'        => $item['price'],
                ]);
                Inventories::where('id', $item['inventory_id'])->increment('stock', (int) $item['qty']);
            }

            return response()->json([
                'status'   => 'success',
                'message'  => 'Transaksi pembelian berhasil disimpan.',
                'redirect' => route('purchases.index'),
                'data'     => $purchase,
            ], 201);
        });
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'number'               => 'nullable|string|max:50',
            'date'                 => 'required|date',
            'items'                => 'required|array|min:1',
            'items.*.inventory_id' => 'required|integer',
            'items.*.qty'          => 'required|integer|min:1',
            'items.*.price'        => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($id, $validated) {
            $purchase = Purchases::find($id);
            if (!$purchase) {
                return response()->json(['status' => 'error', 'message' => 'Purchase transaction not found.'], 404);
            }

            $old_details = PurchaseDetails::where('purchase_id', $id)->get();
            foreach ($old_details as $old) {
                Inventories::where('id', $old->inventory_id)->decrement('stock', (int) $old->qty);
            }

            PurchaseDetails::where('purchase_id', $id)->delete();

            $purchase->update([
                'number' => $validated['number'] ?? $purchase->number,
                'date'   => $validated['date'],
            ]);

            foreach ($validated['items'] as $item) {
                PurchaseDetails::create([
                    'purchase_id'  => $purchase->id,
                    'inventory_id' => $item['inventory_id'],
                    'qty'          => $item['qty'],
                    'price'        => $item['price'],
                ]);
                Inventories::where('id', $item['inventory_id'])->increment('stock', (int) $item['qty']);
            }

            return response()->json([
                'status'   => 'success',
                'message'  => 'Transaksi pembelian berhasil diperbarui.',
                'redirect' => route('purchases.index'),
                'data'     => $purchase,
            ]);
        });
    }

    public function destroy(int $id)
    {
        return DB::transaction(function () use ($id) {
            $purchase = Purchases::find($id);
            if (!$purchase) {
                return response()->json(['status' => 'error', 'message' => 'Purchase transaction not found.'], 404);
            }

            $details = PurchaseDetails::where('purchase_id', $id)->get();
            foreach ($details as $detail) {
                Inventories::where('id', $detail->inventory_id)->decrement('stock', (int) $detail->qty);
            }

            PurchaseDetails::where('purchase_id', $id)->delete();
            $purchase->delete();

            return response()->json(['status' => 'success', 'message' => 'Purchase transaction deleted successfully.']);
        });
    }
}
