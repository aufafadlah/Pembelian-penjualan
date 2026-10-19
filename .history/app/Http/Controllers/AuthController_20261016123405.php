<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    // Tampilkan halaman login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Proses login sederhana (berdasarkan nama/role tanpa password)
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|exists:users,name',
        ]);

        $user = User::where('name', $request->name)->first();

        // Simpan data user ke dalam session
        Session::put('user_id', $user->id);
        Session::put('user_name', $user->name);
        Session::put('user_role', $user->role);

        // Arahkan ke dashboard sesuai role
        return redirect()->route('dashboard')->with('success', 'Berhasil login!');
    }

    // Proses logout
    public function logout()
    {
        Session::flush();
        return redirect()->route('login')->with('success', 'Berhasil logout!');
    }
}