<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Daftar user sesuai pilihan dropdown login
        $users = [
            ['name' => 'Super Administrator', 'role' => 'SuperAdmin'],
            ['name' => 'Pegawai Sales', 'role' => 'Sales'],
            ['name' => 'Pegawai Purchase', 'role' => 'Purchase'],
            ['name' => 'Operational Manager', 'role' => 'Manager'],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['name' => $data['name']],
                [
                    'role' => $data['role'],
                    'password' => Hash::make('password') // Password default aman meski tidak dipakai di form
                ]
            );
        }
    }
}