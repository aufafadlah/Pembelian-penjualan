<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Inventories;
use App\Models\Sales;
use App\Models\SalesDetails;
use App\Models\Purchases;
use App\Models\PurchaseDetails;

class DashboardController extends Controller
{
    public function index()
    {
        $role = session('user_role');
        $user_id = (int) session('user_id');

        $total_inventory = Inventories::count();
        $total_stock = (int) Inventories::sum('stock');

        // Sales Query
        $sales_query = Sales::query();
        $total_sales_count = (clone $sales_query)->count();

        $sale_ids = (clone $sales_query)->pluck('id');
        $total_sales_amount = (float) (SalesDetails::whereIn('sales_id', $sale_ids)
            ->selectRaw('SUM(qty * price) as total')
            ->value('total') ?? 0);

        $recent_sales = (clone $sales_query)->orderBy('id', 'desc')->take(5)->get();
        foreach ($recent_sales as $sale) {
            $sale->details = SalesDetails::where('sales_id', $sale->id)->get();
        }

        // Purchases Query
        $purchases_query = Purchases::query();
        $total_purchases_count = (clone $purchases_query)->count();

        $purchase_ids = (clone $purchases_query)->pluck('id');
        $total_purchases_amount = (float) (PurchaseDetails::whereIn('purchase_id', $purchase_ids)
            ->selectRaw('SUM(qty * price) as total')
            ->value('total') ?? 0);

        $recent_purchases = (clone $purchases_query)->orderBy('id', 'desc')->take(5)->get();
        foreach ($recent_purchases as $purchase) {
            $purchase->details = PurchaseDetails::where('purchase_id', $purchase->id)->get();
        }

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
