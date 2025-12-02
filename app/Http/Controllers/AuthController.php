<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Absensi;
use App\Models\Pegawai; // Import Pegawai untuk memastikan user punya data pegawai

class AuthController extends Controller
{
    // --- CONFIG LOKASI DAN WIFI ---
    private $wifiAllowed = ['matari', 'matari_wifi', 'matari5g'];
    private $latKedai = -7.7775846;
    private $lngKedai = 110.395392;
    private $maxDistance = 200; // Toleransi jarak maksimal dari kedai (meter)

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // DEV bypass WiFi/GPS sementara. Di-set false untuk production.
        $devBypass = true; 

        // Ambil data dari hidden fields di form login (dari client JS)
        $wifi = strtolower($request->wifi_name ?? '');
        $lat  = $request->lat; // Menggunakan 'lat' sesuai input di form HTML sebelumnya
        $lng  = $request->lng; // Menggunakan 'lng' sesuai input di form HTML sebelumnya

        if (!$devBypass) {
            // Cek WiFi lokal
            if (!in_array($wifi, $this->wifiAllowed)) {
                return back()->with('error', 'Anda harus terhubung ke WiFi Matari untuk absensi.');
            }

            // Cek Jarak GPS
            if (!$lat || !$lng) {
                 return back()->with('error', 'Gagal mendapatkan data lokasi GPS. Cek izin lokasi.');
            }
            
            $distance = $this->distance($lat, $lng, $this->latKedai, $this->lngKedai);
            if ($distance > $this->maxDistance) {
                return back()->with('error', 'Anda berada di luar area Kedai Matari (Jarak: ' . round($distance) . 'm).');
            }
        }

        // --- VALIDASI DAN ATTEMPT LOGIN ---
        $creds = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);
        
        if (!auth()->attempt($creds))
            return back()->with('error','Username atau password salah.');

        $user = auth()->user();

        if ($user->role === 'owner')
            return redirect()->route('dashboard');

        // --- AUTO CHECK-IN setelah login ---
        $pegawai = $user->pegawai;
        
        if ($pegawai) {
            $today = Carbon::today()->toDateString();

            // Cek apakah sudah ada sesi check-in hari ini
            $sudahCheckIn = Absensi::where('pegawai_id', $pegawai->id)
                                 ->whereDate('tanggal', $today)
                                 ->whereNull('check_out_at')
                                 ->exists();

            if (!$sudahCheckIn) {
                // Gunakan Carbon::now('Asia/Jakarta') untuk konsistensi timezone
                Absensi::create([
                    'pegawai_id' => $pegawai->id,
                    'tanggal'    => $today,
                    'check_in_at'=> Carbon::now('Asia/Jakarta'),
                    'lokasi_lat' => $lat,
                    'lokasi_long'=> $lng,
                    'status_kehadiran' => 'hadir' // Status awal akan diperbarui di AbsensiController jika perlu
                ]);
            }
        }
        
        return redirect()->route('dashboard');
    }

    /**
     * Menghitung jarak antara dua koordinat GPS (Haversine formula).
     * Hasil dalam meter.
     */
    public function distance($lat1, $lon1, $lat2, $lon2) {
        $earth = 6371000; // Radius Bumi dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2)**2 + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2)**2;

        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        // Logika session invalidation yang lebih aman
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}