<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

use App\Models\Pegawai;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Cuti;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // =========================================================
        // OWNER DASHBOARD
        // =========================================================
        if ($user->role === 'owner') 
        {
            $karyawanAktif = Pegawai::where('status', 'Aktif')->count();

            // cuti yg berlaku hari ini
            $karyawanCuti = Cuti::whereDate('tanggal_mulai', '<=', Carbon::today())
                                ->whereDate('tanggal_selesai', '>=', Carbon::today())
                                ->count();

            // hadir hari ini (distinct pegawai)
            $hadirHariIni = Absensi::where('tanggal', Carbon::today()->toDateString())
                                    ->distinct()
                                    ->count('pegawai_id');

            // stok rendah (jika ada)
            $stokRendah = 0;
            if (Schema::hasTable('stock_dummy'))
            {
                $stokRendah = DB::table('stock_dummy')
                                ->where('qty', '<=', 3)
                                ->count();
            }

            // ambil kalender bulan ini (untuk dashboard owner)
            $month = $request->query('month', now()->month);
            $year  = $request->query('year', now()->year);

            $calendarData = $this->getCalendarData($month, $year);

            return view('dashboard.owner', compact(
                'karyawanAktif',
                'karyawanCuti',
                'hadirHariIni',
                'stokRendah',
                'user',
                'calendarData'
            ));
        }

        // =========================================================
        // PEGAWAI DASHBOARD (role: pegawai, kasir, barista, waiter, chef)
        // =========================================================
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return redirect()->route('settings')
                   ->with('error', 'Akun Anda belum terhubung ke data pegawai.');
        }

        $today = Carbon::today()->toDateString();
        $pegawaiId = $pegawai->id;

        // ----------------------
        // LOAD JADWAL & SHIFT
        // ----------------------
        $jadwal = Jadwal::with('shift')
            ->where('pegawai_id', $pegawaiId)
            ->where('tanggal', $today)
            ->first();

        // ----------------------
        // LOAD SEGMENT ABSENSI
        // ----------------------
        $hadirSesi = Absensi::where('pegawai_id', $pegawaiId)
            ->where('tanggal', $today)   // <= FIX: pakai where, bukan whereDate
            ->orderBy('check_in_at', 'asc')
            ->get();

        // ----------------------
        // HITUNG STATUS
        // ----------------------
        $status = 'Belum Check-in';

        if ($hadirSesi->count() > 0) 
        {
            // pakai status dari segment pertama
            $status = ucfirst($hadirSesi->first()->status_kehadiran);
        } 
        else 
        {
            // belum check-in
            if ($jadwal && $jadwal->shift)
            {
                // waktu shift dimulai (hari ini + jam shift)
                $shiftStart = Carbon::parse($today.' '.$jadwal->shift->start_time);

                // batas alpha = shiftStart + 60 menit
                $alphaStart = $shiftStart->copy()->addMinutes(60);

                // sekarang sudah melewati batas alpha?
                if (Carbon::now()->greaterThan($alphaStart))
                {
                    $status = 'Alpha';
                }
            }
        }

        return view('dashboard.pegawai', compact(
            'user',
            'jadwal',
            'status',
            'hadirSesi',
            'pegawai'
        ));
    }

    // ========================================================
    // API Calendar untuk dashboard owner
    // ========================================================
    public function getCalendarJson(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'owner') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $month = $request->query('month', now()->month);
        $year  = $request->query('year', now()->year);

        return response()->json($this->getCalendarData($month, $year));
    }

    // ========================================================
    // HELPER KALENDER untuk dashboard owner
    // ========================================================
    private function getCalendarData($month, $year)
    {
        Carbon::setLocale('id');

        $firstDay = Carbon::create($year, $month, 1);
        $startOfCalendar = $firstDay->copy()->startOfWeek(Carbon::MONDAY);

        $days = [];
        $totalPegawai = Pegawai::where('status', 'Aktif')->count();

        // ambil absensi bulan ini
        $absensiBulanIni = Absensi::select('tanggal', 'status_kehadiran')
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->get()
            ->groupBy(function($i){
                return Carbon::parse($i->tanggal)->format('Y-m-d');
            });

        // loop 42 hari (6 minggu)
        for ($i = 0; $i < 42; $i++)
        {
            $date = $startOfCalendar->copy()->addDays($i);
            $dateStr = $date->toDateString();

            $dots = [];
            $hadirCount = 0;
            $terlambatCount = 0;
            $alphaCount = 0;

            if (isset($absensiBulanIni[$dateStr]))
            {
                $data = $absensiBulanIni[$dateStr];

                $hadirCount    = $data->whereIn('status_kehadiran', ['hadir', 'pengganti'])->count();
                $terlambatCount = $data->where('status_kehadiran','terlambat')->count();
                $alphaCount     = $data->where('status_kehadiran','alpha')->count();

                $statuses = $data->pluck('status_kehadiran')->unique()->toArray();

                if (in_array('alpha', $statuses))     $dots[] = 'red';
                if (in_array('terlambat', $statuses)) $dots[] = 'yellow';
                if (in_array('hadir', $statuses) || in_array('pengganti', $statuses)) $dots[] = 'green';
            }

            $days[] = [
                'label' => $date->day,
                'date'  => $dateStr,
                'dots'  => array_slice(array_unique($dots), 0, 3),
                'summary' => "$hadirCount/$totalPegawai",
                'isCurrentMonth' => $date->month === $firstDay->month,
                'isToday' => $date->isToday(),
            ];
        }

        return [
            'calendar' => $days,
            'currentMonthName' => $firstDay->isoFormat('MMMM YYYY'),
            'currentMonth' => $firstDay->month,
            'currentYear' => $firstDay->year,
            'initialMonth' => $firstDay->month,
            'initialYear' => $firstDay->year,
        ];
    }
}
