<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sales;
use App\Models\SalesDetails;
use App\Models\Inventories;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function data(Request $request)
    {
        $query = Sales::query()->whereNull('deleted_at');

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
        $sales = $query->get();

        $formatted_data = [];
        $row_number = $start + 1;

        foreach ($sales as $sale) {
            $details      = SalesDetails::where('sales_id', $sale->id)->get();
            $total_amount = 0;
            $item_count   = 0;
            foreach ($details as $d) {
                $total_amount += ((int) $d->qty * (float) $d->price);
                $item_count   += (int) $d->qty;
            }
            $user = Users::find($sale->user_id);

            $formatted_data[] = [
                'row_number'   => $row_number++,
                'id'           => $sale->id,
                'number'       => $sale->number,
                'date'         => $sale->date ? Carbon::parse($sale->date)->format('Y-m-d') : '-',
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
        $sale = Sales::find($id);
        if (!$sale) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sales transaction not found.',
            ], 404);
        }

        $details         = SalesDetails::where('sales_id', $id)->get();
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

        $user = Users::find($sale->user_id);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'        => $sale->id,
                'number'    => $sale->number,
                'date'      => $sale->date,
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
            foreach ($validated['items'] as $item) {
                $inv = Inventories::lockForUpdate()->find($item['inventory_id']);
                if (!$inv) {
                    return response()->json(['status' => 'error', 'message' => "Item inventory #{$item['inventory_id']} tidak ditemukan."], 422);
                }
                if ($inv->stock < $item['qty']) {
                    return response()->json(['status' => 'error', 'message' => "Stok {$inv->name} tidak mencukupi. Tersedia: {$inv->stock}, Diminta: {$item['qty']}."], 422);
                }
            }

            $invoice_number = $validated['number'] ?? null;
            if (empty($invoice_number)) {
                $latest         = Sales::withTrashed()->orderBy('id', 'desc')->first();
                $next_id        = $latest ? ($latest->id + 1) : 1;
                $invoice_number = 'INV-' . date('Ymd') . '-' . str_pad((string) $next_id, 4, '0', STR_PAD_LEFT);
            }

            $sale = Sales::create([
                'number'  => $invoice_number,
                'date'    => $validated['date'],
                'user_id' => $user_id,
            ]);

            foreach ($validated['items'] as $item) {
                SalesDetails::create([
                    'sales_id'     => $sale->id,
                    'inventory_id' => $item['inventory_id'],
                    'qty'          => $item['qty'],
                    'price'        => $item['price'],
                ]);
                Inventories::where('id', $item['inventory_id'])->decrement('stock', (int) $item['qty']);
            }

            return response()->json([
                'status'   => 'success',
                'message'  => 'Transaksi penjualan berhasil disimpan.',
                'redirect' => route('sales.index'),
                'data'     => $sale,
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
            $sale = Sales::find($id);
            if (!$sale) {
                return response()->json(['status' => 'error', 'message' => 'Sales transaction not found.'], 404);
            }

            $old_details = SalesDetails::where('sales_id', $id)->get();
            foreach ($old_details as $old) {
                Inventories::where('id', $old->inventory_id)->increment('stock', (int) $old->qty);
            }

            foreach ($validated['items'] as $item) {
                $inv = Inventories::lockForUpdate()->find($item['inventory_id']);
                if (!$inv) {
                    return response()->json(['status' => 'error', 'message' => "Item inventory #{$item['inventory_id']} tidak ditemukan."], 422);
                }
                if ($inv->stock < $item['qty']) {
                    return response()->json(['status' => 'error', 'message' => "Stok {$inv->name} tidak mencukupi. Tersedia: {$inv->stock}, Diminta: {$item['qty']}."], 422);
                }
            }

            SalesDetails::where('sales_id', $id)->delete();

            $sale->update([
                'number' => $validated['number'] ?? $sale->number,
                'date'   => $validated['date'],
            ]);

            foreach ($validated['items'] as $item) {
                SalesDetails::create([
                    'sales_id'     => $sale->id,
                    'inventory_id' => $item['inventory_id'],
                    'qty'          => $item['qty'],
                    'price'        => $item['price'],
                ]);
                Inventories::where('id', $item['inventory_id'])->decrement('stock', (int) $item['qty']);
            }

            return response()->json([
                'status'   => 'success',
                'message'  => 'Transaksi penjualan berhasil diperbarui.',
                'redirect' => route('sales.index'),
                'data'     => $sale,
            ]);
        });
    }

    public function destroy(int $id)
    {
        return DB::transaction(function () use ($id) {
            $sale = Sales::find($id);
            if (!$sale) {
                return response()->json(['status' => 'error', 'message' => 'Sales transaction not found.'], 404);
            }

            $details = SalesDetails::where('sales_id', $id)->get();
            foreach ($details as $detail) {
                Inventories::where('id', $detail->inventory_id)->increment('stock', (int) $detail->qty);
            }

            SalesDetails::where('sales_id', $id)->delete();
            $sale->delete();

            return response()->json(['status' => 'success', 'message' => 'Sales transaction deleted successfully.']);
        });
    }
}
