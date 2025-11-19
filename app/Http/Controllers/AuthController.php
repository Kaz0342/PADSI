<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session; 

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt([
            'username' => $request->username,
            'password' => $request->password
        ], $request->filled('remember'))) {
            return back()->withInput($request->only('username'))->with('error', 'Username atau password salah!');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (in_array($user->role, ['owner', 'manajer', 'pegawai'])) {
            return redirect()->route('dashboard');
        }

        Auth::logout();
        return redirect()->route('login')->with('error', 'Role user tidak dikenali atau tidak memiliki akses.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        Session::forget('user'); 

        return redirect()->route('login');
    }
}