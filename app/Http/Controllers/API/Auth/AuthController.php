<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Users;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function users()
    {
        $userModel = class_exists(User::class) ? User::class : Users::class;
        $users = $userModel::orderBy('name', 'asc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $users,
        ]);
    }

    public function login(Request $request)
    {
        $validated_data = $request->validate([
            'name' => 'required|string',
        ]);

        $userModel = class_exists(User::class) ? User::class : Users::class;
        $user = $userModel::where('name', $validated_data['name'])->first();

        if ($user) {
            session([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful as ' . $user->name,
                'redirect' => route('dashboard'),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'User not found.',
        ], 401);
    }

    public function user(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => session('user_id'),
                'name' => session('user_name'),
                'role' => session('user_role'),
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
            'redirect' => route('login'),
        ]);
    }
}
