<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function users()
    {
        $users = User::orderBy('name', 'asc')->get();
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

        $user = User::where('name', $validated_data['name'])->first();

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
}
