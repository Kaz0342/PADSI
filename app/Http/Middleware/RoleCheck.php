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

        $user = Auth::user();

        if (!$roles) {
            return $next($request);
        }

        $allowed = array_map('trim', explode(',', $roles));

        if (!in_array($user->role, $allowed)) {
            // kalau bukan role yang diizinkan -> forbidden atau redirect
            abort(403, 'Anda tidak memiliki akses.');
        }

        return $next($request);
    }
}
