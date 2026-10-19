<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function data(Request $request)
    {
        $table_data = Inventory::getDataTable($request);
        return response()->json($table_data);
    }

    public function items()
    {
        $items = Inventory::getSelectOptions();
        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }

    public function show(int $id)
    {
        $inventory = Inventory::find($id);
        if (!$inventory) {
            return response()->json([
                'status' => 'error',
                'message' => 'Inventory item not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $inventory,
        ]);
    }

    public function store(Request $request)
    {
        $validated_data = $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $inventory = Inventory::storeInventory($validated_data);

        return response()->json([
            'status' => 'success',
            'message' => 'Inventory item created successfully.',
            'data' => $inventory,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $validated_data = $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $inventory = Inventory::updateInventory($id, $validated_data);

        if (!$inventory) {
            return response()->json([
                'status' => 'error',
                'message' => 'Inventory item not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Inventory item updated successfully.',
            'data' => $inventory,
        ]);
    }

    public function destroy(int $id)
    {
        $is_deleted = Inventory::deleteInventory($id);

        if (!$is_deleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'Inventory item not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Inventory item deleted successfully.',
        ]);
    }
}
