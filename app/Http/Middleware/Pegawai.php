<?php

namespace App\Http\Middleware;

use Closure;

class Pegawai
{
    public function handle($request, Closure $next)
    {
        if (session('user')->role !== 'pegawai') {
            return redirect('/dashboard')->with('error', 'Tidak punya akses');
        }
        return $next($request);
    }
}
