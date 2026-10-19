<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function data(Request $request)
    {
        $user_id = (int) session('user_id');
        $user_role = (string) session('user_role');

        $table_data = Purchase::getDataTable($request, $user_id, $user_role);
        return response()->json($table_data);
    }

    public function show(int $id)
    {
        $purchase = Purchase::with('details')->find($id);
        if (!$purchase) {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase transaction not found.',
            ], 404);
        }

        $formatted_items = [];
        foreach ($purchase->details as $detail) {
            $inventory = $detail->getInventory();
            $formatted_items[] = [
                'id' => $detail->id,
                'inventory_id' => $detail->inventory_id,
                'inventory_name' => $inventory ? $inventory->name : '-',
                'inventory_code' => $inventory ? $inventory->code : '-',
                'qty' => (int) $detail->qty,
                'price' => (float) $detail->price,
                'subtotal' => (int) $detail->qty * (float) $detail->price,
            ];
        }

        $user = $purchase->getUser();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $purchase->id,
                'number' => $purchase->number,
                'date' => $purchase->date,
                'user_name' => $user ? $user->name : '-',
                'items' => $formatted_items,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated_data = $request->validate([
            'number' => 'nullable|string|max:50',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.inventory_id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $user_id = (int) session('user_id');
        $purchase = Purchase::storePurchase($validated_data, $user_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase transaction created successfully.',
            'redirect' => route('purchases.index'),
            'data' => $purchase,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $validated_data = $request->validate([
            'number' => 'nullable|string|max:50',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.inventory_id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $purchase = Purchase::updatePurchase($id, $validated_data);

        if (!$purchase) {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase transaction not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase transaction updated successfully.',
            'redirect' => route('purchases.index'),
            'data' => $purchase,
        ]);
    }

    public function destroy(int $id)
    {
        $is_deleted = Purchase::deletePurchase($id);

        if (!$is_deleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase transaction not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase transaction deleted successfully.',
        ]);
    }
}
