<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use
use Illuminate\Support\Facades\DB;

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
        $sale = DB::table('sales')->where('id', $id)->whereNull('deleted_at')->first();
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
        $sale = DB::table('sales')->where('id', $id)->whereNull('deleted_at')->first();
        if (!$sale) {
            return redirect()->route('sales.index');
        }

        $sale->created_at = Carbon::parse($sale->created_at);

        $details = DB::table('sales_details')
            ->where('sales_id', $id)
            ->get()
            ->map(function ($d) {
                $inv = DB::table('inventories')->where('id', $d->inventory_id)->first();
                return new class($d, $inv) {
                    public $id;
                    public $sales_id;
                    public $inventory_id;
                    public $qty;
                    public $price;
                    private $inv;

                    public function __construct($d, $inv) {
                        $this->id = $d->id;
                        $this->sales_id = $d->sales_id;
                        $this->inventory_id = $d->inventory_id;
                        $this->qty = $d->qty;
                        $this->price = $d->price;
                        $this->inv = $inv;
                    }

                    public function getInventory() {
                        return $this->inv;
                    }
                };
            });

        $sale->details = $details;
        $user = DB::table('users')->where('id', $sale->user_id)->first();

        return view('sales.print', [
            'sale' => $sale,
            'user' => $user,
        ]);
    }
}
