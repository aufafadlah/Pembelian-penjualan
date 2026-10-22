<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Purchases;
use App\Models\PurchaseDetails;
use App\Models\Users;
use Carbon\Carbon;

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
        $purchase = Purchases::find($id);
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
        $purchase = Purchases::find($id);
        if (!$purchase) {
            return redirect()->route('purchases.index');
        }

        $purchase->created_at = Carbon::parse($purchase->created_at);
        $purchase->details = PurchaseDetails::where('purchase_id', $id)->get();
        $user = Users::find($purchase->user_id);

        return view('purchase.print', [
            'purchase' => $purchase,
            'user' => $user,
        ]);
    }
}
