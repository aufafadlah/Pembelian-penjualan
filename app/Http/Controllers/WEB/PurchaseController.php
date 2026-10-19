<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Purchase;

class PurchaseController extends Controller
{
    public function index()
    {
        return view('purchase.index');
    }

    public function create()
    {
        return view('purchase.form', [
            'mode' => 'create',
            'purchase_id' => null,
        ]);
    }

    public function edit(int $id)
    {
        $purchase = Purchase::find($id);
        if (!$purchase) {
            return redirect()->route('purchases.index');
        }

        return view('purchase.form', [
            'mode' => 'edit',
            'purchase_id' => $id,
        ]);
    }

    public function print(int $id)
    {
        $purchase = Purchase::with('details')->find($id);
        if (!$purchase) {
            return redirect()->route('purchases.index');
        }

        $user = $purchase->getUser();

        return view('purchase.print', [
            'purchase' => $purchase,
            'user' => $user,
        ]);
    }
}
