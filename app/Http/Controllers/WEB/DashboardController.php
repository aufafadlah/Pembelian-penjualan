<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Purchase;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        $role = session('user_role');
        $user_id = (int) session('user_id');

        $total_inventory = Inventory::count();
        $total_stock = (int) Inventory::sum('stock');

        $sales_query = Sale::query();
        if ($role === 'Sales') {
            $sales_query->where('user_id', $user_id);
        }
        $total_sales_count = $sales_query->count();

        $recent_sales = (clone $sales_query)->with('details')->orderBy('id', 'desc')->take(5)->get();
        $total_sales_amount = (clone $sales_query)->with('details')->get()->sum(function ($sale) {
            return $sale->details->sum(function ($detail) {
                return (int) $detail->qty * (float) $detail->price;
            });
        });

        $purchases_query = Purchase::query();
        if ($role === 'Purchase') {
            $purchases_query->where('user_id', $user_id);
        }
        $total_purchases_count = $purchases_query->count();

        $recent_purchases = (clone $purchases_query)->with('details')->orderBy('id', 'desc')->take(5)->get();
        $total_purchases_amount = (clone $purchases_query)->with('details')->get()->sum(function ($purchase) {
            return $purchase->details->sum(function ($detail) {
                return (int) $detail->qty * (float) $detail->price;
            });
        });

        return view('dashboard', compact(
            'role',
            'total_inventory',
            'total_stock',
            'total_sales_count',
            'total_sales_amount',
            'total_purchases_count',
            'total_purchases_amount',
            'recent_sales',
            'recent_purchases'
        ));
    }
}
