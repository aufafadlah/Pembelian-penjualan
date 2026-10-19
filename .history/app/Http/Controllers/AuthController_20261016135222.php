<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    // Menampilkan halaman login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Memproses data login via AJAX dropdown
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        // Cari user berdasarkan nama/role yang dipilih dari dropdown
        $user = User::where('name', $request->name)->first();

        if ($user) {
            // Simpan session login
            session([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Login Berhasil sebagai ' . $user->name,
                'redirect' => route('dashboard')
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'User tidak valid atau tidak ditemukan.'
        ], 401);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login');
    }
}