<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Super Admin', 'email' => 'superadmin@mail.com', 'role' => 'superadmin'],
            ['name' => 'Sales',       'email' => 'sales@mail.com',      'role' => 'sales'],
            ['name' => 'Purchase',    'email' => 'purchase@mail.com',   'role' => 'purchase'],
            ['name' => 'Manager',     'email' => 'manager@mail.com',    'role' => 'manager'],
        ];

        foreach ($users as $u) {
            User::create($u + ['password' => Hash::make('password')]);
        }

        $items = [
            ['code' => 'BRG-001', 'name' => 'Laptop',   'price' => 8000000, 'stock' => 20],
            ['code' => 'BRG-002', 'name' => 'Mouse',    'price' => 150000,  'stock' => 100],
            ['code' => 'BRG-003', 'name' => 'Keyboard', 'price' => 350000,  'stock' => 50],
        ];

        foreach ($items as $i) {
            Inventory::create($i);
        }
    }
}