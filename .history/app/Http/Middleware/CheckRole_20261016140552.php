<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // belum login
        if (! session()->has('user_id')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi berakhir, silakan login ulang'], 401);
            }
            return redirect()->route('login');
        }

        // role tidak sesuai (jika middleware diberi daftar role)
        if ($roles && ! in_array(session('user_role'), $roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}