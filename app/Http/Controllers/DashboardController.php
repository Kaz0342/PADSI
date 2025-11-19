<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\Pegawai;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Cuti;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Auth; 

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        if ($user->role === 'owner') {

            $karyawanAktif = Pegawai::where('status', 'Aktif')->count();

            $karyawanCuti = Cuti::whereDate('tanggal_mulai', '<=', Carbon::today())
                                 ->whereDate('tanggal_selesai', '>=', Carbon::today())
                                 ->count();

            $hadirHariIni = Absensi::whereDate('check_in_at', Carbon::today())
                                 ->distinct()
                                 ->count('pegawai_id');

            $stokRendah = 0;
            if (Schema::hasTable('stock_dummy')) {
                $stokRendah = DB::table('stock_dummy')->where('qty', '<=', 3)->count();
            }

            return view('dashboard.owner', compact(
                'karyawanAktif',
                'karyawanCuti',
                'hadirHariIni',
                'stokRendah',
                'user'
            ));
        }

        $pegawaiId = $user->pegawai?->id ?? $user->id; 

        $today = Carbon::today()->toDateString();

        $jadwal = Jadwal::where('pegawai_id', $pegawaiId)
                         ->where('tanggal', $today)
                         ->first();

        $hadirSesi = Absensi::where('pegawai_id', $pegawaiId)
                             ->whereDate('check_in_at', $today)
                             ->get();

        $status = 'Belum Check-in';

        if ($hadirSesi->count() > 0) {
            $status = 'Hadir';
        } elseif ($jadwal) {
            
            $mulaiWaktu = $jadwal->mulai; 
            
            if (!$mulaiWaktu && $jadwal->shift) {
                $mulaiWaktu = $jadwal->shift->start_time;
            }

            if ($mulaiWaktu) {
                $mulai = Carbon::parse($mulaiWaktu);
                
                if (Carbon::now()->greaterThan($mulai->copy()->addHour())) {
                    $status = 'Tidak Hadir';
                }
            } else {
                $status = 'Jadwal Error'; 
            }
        }

        return view('dashboard.pegawai', compact(
            'user',
            'jadwal',
            'status',
            'hadirSesi'
        ));
    }
}