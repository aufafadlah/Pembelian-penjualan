<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user_list = [
            ['name' => 'Super Administrator', 'role' => 'SuperAdmin'],
            ['name' => 'Pegawai Sales', 'role' => 'Sales'],
            ['name' => 'Pegawai Purchase', 'role' => 'Purchase'],
            ['name' => 'Operational Manager', 'role' => 'Manager'],
        ];

        foreach ($user_list as $user_data) {
            User::create($user_data);
        }

        $inventory_list = [
            ['code' => 'BRG-001', 'name' => 'Laptop ASUS Vivobook', 'price' => 8500000, 'stock' => 20],
            ['code' => 'BRG-002', 'name' => 'Mouse Wireless Logitech', 'price' => 175000, 'stock' => 100],
            ['code' => 'BRG-003', 'name' => 'Mechanical Keyboard RGB', 'price' => 450000, 'stock' => 50],
            ['code' => 'BRG-004', 'name' => 'Monitor LED 24 Inch IPS', 'price' => 1650000, 'stock' => 30],
            ['code' => 'BRG-005', 'name' => 'Headset Gaming 7.1', 'price' => 320000, 'stock' => 45],
        ];

        foreach ($inventory_list as $inventory_data) {
            Inventory::create($inventory_data);
        }
    }
}