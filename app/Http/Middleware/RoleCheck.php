<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleCheck
{
    public function handle(Request $request, Closure $next, $roles = null)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        // bypass CSRF preflight / form submit tertentu
        if ($request->is('absensi/checkout')) {
            return $next($request);
        }


        $user = Auth::user();

        if ($roles) {
            $allowed = array_map('trim', explode(',', $roles));

            // Cek apakah role user ada di dalam daftar role yang diizinkan
            if (!in_array($user->role, $allowed)) {
                return abort(403, 'Anda tidak memiliki akses.');
            }
        }

        return $next($request);
    }
}