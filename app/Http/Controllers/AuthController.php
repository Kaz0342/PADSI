<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\TanggalKerja;
use App\Services\ShiftService;

class AuthController extends Controller
{
    /* ============================================================
       KONFIGURASI LOKASI & WIFI
    ============================================================ */
    private bool $devBypass = true;
    private int $maxDistance = 200;

    private float $latKedai = -7.7775846;
    private float $lngKedai = 110.395392;

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        /* ==========================
           1. VALIDASI LOGIN
        ========================== */
        $creds = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        /* ==========================
           2. WIFI & GPS (OPTIONAL)
        ========================== */
        if (!$this->devBypass) {
            if ($request->wifi_connected != 1) {
                return back()->with('error', 'Harus terhubung ke WiFi Kedai.');
            }

            if (!$request->lat || !$request->lng) {
                return back()->with('error', 'GPS tidak terbaca.');
            }

            $distance = $this->distance(
                $request->lat,
                $request->lng,
                $this->latKedai,
                $this->lngKedai
            );

            if ($distance > $this->maxDistance) {
                return back()->with('error', 'Di luar area kedai.');
            }
        }

        /* ==========================
           3. AUTH
        ========================== */
        if (!Auth::attempt($creds)) {
            return back()->with('error', 'Username atau password salah.');
        }

        $user = Auth::user();

        /* ==========================
           4. OWNER → DASHBOARD
        ========================== */
        if ($user->role === 'owner') {
            return redirect()->route('dashboard');
        }

        /* ==========================
           5. PEGAWAI FLOW
        ========================== */
        $pegawai = $user->pegawai;
        if (!$pegawai) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Akun tidak terhubung ke pegawai.');
        }

        $today = Carbon::today('Asia/Jakarta');
        $now   = Carbon::now('Asia/Jakarta');

        /* ==========================
           SATPAM UTAMA:
           SUDAH ADA ABSENSI?
        ========================== */
        $existing = Absensi::where('pegawai_id', $pegawai->id)
            ->where('tanggal', $today->toDateString())
            ->first();

        if ($existing) {
            return redirect()->route('dashboard');
        }

        /* ==========================
           TOKO BUKA?
        ========================== */
        $tglKerja = TanggalKerja::where('tanggal', $today->toDateString())->first();
        if (!$tglKerja || !$tglKerja->is_open) {
            return redirect()->route('dashboard')
                ->with('info', 'Hari ini toko tutup.');
        }

        /* ==========================
           HARI SENIN (FREE PASS)
        ========================== */
        if ($today->isMonday()) {
            Absensi::create([
                'pegawai_id'       => $pegawai->id,
                'tanggal'          => $today->toDateString(),
                'check_in_at'      => $now,
                'status_kehadiran' => 'hadir',
                'tipe_sesi'        => 'normal',
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Auto hadir (Senin).');
        }

        /* ==========================
           CEK JADWAL SHIFT
        ========================== */
        $shift = ShiftService::getShiftForPegawai($pegawai->id);

        if ($shift) {
            // cek jam & status
            $start = Carbon::parse($today->toDateString().' '.$shift->start_time);
            $diff  = $start->diffInMinutes($now, false);

            $status = $diff <= 0
                ? 'hadir'
                : ($diff <= 30 ? 'terlambat' : 'alpha');

            Absensi::create([
                'pegawai_id'       => $pegawai->id,
                'tanggal'          => $today->toDateString(),
                'check_in_at'      => $now,
                'status_kehadiran' => $status,
                'tipe_sesi'        => 'normal',
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Check-in otomatis: '.ucfirst($status));
        }

        /* ==========================
           TIDAK ADA JADWAL → PENGGANTI
        ========================== */
        $abs = Absensi::create([
            'pegawai_id'       => $pegawai->id,
            'tanggal'          => $today->toDateString(),
            'check_in_at'      => $now,
            'status_kehadiran' => 'hadir',
            'tipe_sesi'        => 'pengganti',
        ]);

        Session::put('absensi_id', $abs->id);

        return redirect()->route('absensi.pengganti.form')
            ->with('info', 'Anda tidak terjadwal hari ini.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /* ============================================================
       HAVERSINE
    ============================================================ */
    private function distance($lat1, $lon1, $lat2, $lon2)
    {
        $earth = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2 +
             cos(deg2rad($lat1)) *
             cos(deg2rad($lat2)) *
             sin($dLon / 2) ** 2;

        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
