<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;

class InventoryController extends Controller
{
    public function index()
    {
        return view('data_master.inventory.index');
    }
}
