<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Pegawai
{
    public function handle($request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->role !== 'pegawai') {
            abort(403);
        }

        return $next($request);
    }
}
