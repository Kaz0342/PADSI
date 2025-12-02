<?php

namespace App\Http\Middleware;

use Closure;

class Owner
{
    public function handle($request, Closure $next)
    {
        if (session('user')->role !== 'Owner') {
            return redirect('/dashboard')->with('error', 'Tidak punya akses');
        }
        return $next($request);
    }
}
