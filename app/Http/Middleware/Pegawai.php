<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Pegawai
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'pegawai') {
            abort(403, 'Akses khusus pegawai.');
        }

        return $next($request);
    }
}
