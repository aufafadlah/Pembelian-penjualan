<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('user_id')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated_data = $request->validate([
            'name' => 'required|string',
        ]);

        $user = \App\Models\Users::where('name', $validated_data['name'])->first();

        if ($user) {
            session([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Login successful as ' . $user->name,
                    'redirect' => route('dashboard'),
                ]);
            }

            return redirect()->route('dashboard');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 401);
        }

        return back()->withErrors(['name' => 'User not found.']);
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login');
    }
}
