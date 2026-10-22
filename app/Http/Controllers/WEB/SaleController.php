<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Sales;
use App\Models\SalesDetails;
use App\Models\Users;
use Carbon\Carbon;

class SaleController extends Controller
{
    public function index()
    {
        return view('sales.index');
    }

    public function create()
    {
        return view('sales.form', [
            'mode' => 'create',
            'sale_id' => null,
        ]);
    }

    public function edit(int $id)
    {
        $sale = Sales::find($id);
        if (!$sale) {
            return redirect()->route('sales.index');
        }

        return view('sales.form', [
            'mode' => 'edit',
            'sale_id' => $id,
        ]);
    }

    public function print(int $id)
    {
        $sale = Sales::find($id);
        if (!$sale) {
            return redirect()->route('sales.index');
        }

        $sale->created_at = Carbon::parse($sale->created_at);
        $sale->details = SalesDetails::where('sales_id', $id)->get();
        $user = Users::find($sale->user_id);

        return view('sales.print', [
            'sale' => $sale,
            'user' => $user,
        ]);
    }
}
