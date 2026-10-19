<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Sale;

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
        $sale = Sale::find($id);
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
        $sale = Sale::with('details')->find($id);
        if (!$sale) {
            return redirect()->route('sales.index');
        }

        $user = $sale->getUser();

        return view('sales.print', [
            'sale' => $sale,
            'user' => $user,
        ]);
    }
}
