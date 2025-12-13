<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleCheck
{
    // Perhatikan "...$roles". Ini trik biar lo bisa kirim banyak role di route
    // Contoh: Route::get(...)->middleware('role:owner,admin,pegawai');
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // 1. Cek Login dulu (Ambil logic dari Snippet B - Lebih ramah user)
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 2. Cek Role (Ambil logic Snippet A - Lebih ringkas)
        // Karena pake '...$roles', variable $roles otomatis udah jadi array.
        // Gak perlu explode-explode club lagi.
        
        // Kita cek, array roles-nya kosong gak? Kalau di route gak didefine, anggap lolos aja.
        if (!empty($roles)) {
            // Cek apakah role user ada di dalam daftar role yang dibolehkan
            if (!in_array($user->role, $roles)) {
                // Kalau gak ada, tendang.
                return abort(403, 'Anda tidak memiliki akses. Minggir lu miskin.'); 
            }
        }

        return $next($request);
    }
}