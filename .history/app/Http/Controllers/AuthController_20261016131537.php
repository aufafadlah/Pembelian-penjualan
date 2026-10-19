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

    // Proses login dengan AJAX
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|exists:users,name',
        ], [
            'name.required' => 'Silakan pilih user terlebih dahulu.',
            'name.exists' => 'User tidak ditemukan di database.'
        ]);

        $user = User::where('name', $request->name)->first();

        // Simpan data ke session
        Session::put('user_id', $user->id);
        Session::put('user_name', $user->name);
        Session::put('user_role', $user->role);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil login! Mengalihkan...',
            'redirect' => route('dashboard')
        ]);
    }

    // Proses logout
    public function logout()
    {
        Session::flush();
        return redirect()->route('login')->with('success', 'Berhasil logout!');
    }
}