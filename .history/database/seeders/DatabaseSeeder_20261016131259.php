<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Data user sesuai role pada soal
        $users = [
            ['name' => 'Super Administrator', 'role' => 'SuperAdmin'],
            ['name' => 'Pegawai Sales', 'role' => 'Sales'],
            ['name' => 'Pegawai Purchase', 'role' => 'Purchase'],
            ['name' => 'Operational Manager', 'role' => 'Manager'],
        ];

        foreach ($users as $u) {
            User::create($u);
        }

        // Data barang awal (Inventories)
        $items = [
            ['code' => 'BRG-001', 'name' => 'Laptop', 'price' => 8000000, 'stock' => 20],
            ['code' => 'BRG-002', 'name' => 'Mouse', 'price' => 150000, 'stock' => 100],
            ['code' => 'BRG-003', 'name' => 'Keyboard', 'price' => 350000, 'stock' => 50],
        ];

        foreach ($items as $i) {
            Inventory::create($i);
        }
    }
}