<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Owner
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'owner') {
            abort(403, 'Akses khusus owner.');
        }

        return $next($request);
    }
}
