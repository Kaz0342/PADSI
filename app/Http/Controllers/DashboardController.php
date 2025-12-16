<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

// MODELS
use App\Models\Pegawai;
use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\AbsensiPengganti;
use App\Models\Shift; 

class DashboardController extends Controller
{
    /* ==========================================================
       DASHBOARD ENTRY
    ========================================================== */
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        return $user->role === 'owner'
            ? $this->ownerDashboard()
            : $this->pegawaiDashboard();
    }

    /* ==========================================================
       OWNER DASHBOARD
    ========================================================== */
    protected function ownerDashboard()
    {
        $today = Carbon::today('Asia/Jakarta')->toDateString();

        return view('dashboard.owner', [
            'karyawanAktif' => Pegawai::where('status', 'Aktif')->count(),
            'karyawanCuti'  => Cuti::whereDate('tanggal_mulai', '<=', $today)
                                    ->whereDate('tanggal_selesai', '>=', $today)
                                    ->count(),
            'hadirHariIni'  => Absensi::where('tanggal', $today)
                                    ->whereIn('status_kehadiran', ['hadir','terlambat','pengganti'])
                                    ->distinct('pegawai_id')
                                    ->count('pegawai_id'),
            'stokRendah'    => Schema::hasTable('stock_dummy')
                                    ? DB::table('stock_dummy')->where('qty','<=',3)->count()
                                    : 0
        ]);
    }

    /* ==========================================================
       PEGAWAI DASHBOARD
    ========================================================== */
    protected function pegawaiDashboard()
    {
        $pegawai = Auth::user()->pegawai;
        if (!$pegawai) {
            return redirect()->route('login');
        }

        $today = Carbon::today('Asia/Jakarta')->toDateString();

        $todayAbsensi = Absensi::where('pegawai_id', $pegawai->id)
            ->where('tanggal', $today)
            ->latest()
            ->first();

        $active = Absensi::where('pegawai_id', $pegawai->id)
            ->whereNull('check_out_at')
            ->latest()
            ->first();

        $hadirSesi = Absensi::where('pegawai_id', $pegawai->id)
            ->where('tanggal', $today)
            ->orderBy('check_in_at')
            ->get();

        return view('dashboard.pegawai', [
            'pegawai' => $pegawai,
            'todayAbsensi' => $todayAbsensi,
            'active' => $active,
            'hadirSesi' => $hadirSesi,

            // 🔒 LOCK MODE — ABSENSI DASAR
            'shift' => null,
            'mustPengganti' => false,
            'statusAbsen' => null,
            'isEarly' => false,
            'endTimeFormatted' => null,
        ]);
    }

    /* ==========================================================
       📅 CALENDAR JSON — FINAL & ADAPTIF
       WEEK START = SUNDAY (SESUAI UI)
    ========================================================== */
    public function getCalendarJson(Request $request)
    {
        abort_unless(auth()->user()->role === 'owner', 403);

        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year ?? now()->year);

        // Kalender mulai SENIN – selesai MINGGU
        $start = Carbon::create($year, $month, 1)
            ->startOfMonth()
            ->startOfWeek(Carbon::SUNDAY); // ⬅️ PENTING

        $end = Carbon::create($year, $month, 1)
            ->endOfMonth()
            ->endOfWeek(Carbon::SATURDAY);


        $absensi = Absensi::whereBetween('tanggal', [
            $start->toDateString(),
            $end->toDateString()
        ])->get()->groupBy(fn ($a) => $a->tanggal->toDateString());

        $days = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $date = $cursor->toDateString();
            $rows = $absensi[$date] ?? collect();

            $days[] = [
                'date'           => $date,
                'dayNumber'      => (int) $cursor->day,          // 🔥 WAJIB
                'isCurrentMonth' => $cursor->month === $month,   // 🔥 WAJIB
                'isToday'        => $cursor->isToday(),          // 🔥 WAJIB
                'hadir'          => $rows->whereIn('status_kehadiran', ['hadir','pengganti'])->count(),
                'terlambat'      => $rows->where('status_kehadiran','terlambat')->count(),
                'alpha'          => $rows->where('status_kehadiran','alpha')->count(),
            ];

            $cursor->addDay();
        }

        return response()->json($days);
    }

    /* ==========================================================
       📄 DETAIL PER TANGGAL
    ========================================================== */
    public function getRekapDetailJson(Request $request)
    {
        abort_unless(auth()->user()->role === 'owner', 403);

        $date = $request->date;

        $rows = Absensi::with('pegawai:id,nama,jabatan')
            ->whereDate('tanggal', $date)
            ->get();

        return response()->json([
            'date_formatted' => Carbon::parse($date)->translatedFormat('d F Y'),
            'summary' => [
                'hadir'     => $rows->whereIn('status_kehadiran',['hadir','pengganti'])->count(),
                'terlambat' => $rows->where('status_kehadiran','terlambat')->count(),
                'alpha'     => $rows->where('status_kehadiran','alpha')->count(),
            ],
            'rows' => $rows->map(fn($r) => [
                'nama'      => $r->pegawai->nama,
                'posisi'    => $r->pegawai->jabatan,
                'status'    => $r->status_kehadiran,
                'check_in'  => optional($r->check_in_at)->format('H:i'),
                'check_out' => optional($r->check_out_at)->format('H:i'),
                'catatan'   => $r->catatan,
            ])
        ]);
    }

    /* ==========================================================
       📊 STATS
    ========================================================== */
    public function stats(Request $request)
    {
        abort_unless(Auth::user()->role === 'owner', 403);

        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year  ?? now()->year);

        $leaderboard = Absensi::select('pegawai_id', DB::raw('COUNT(*) as total'))
            ->whereMonth('tanggal',$month)
            ->whereYear('tanggal',$year)
            ->whereIn('status_kehadiran',['hadir','pengganti'])
            ->groupBy('pegawai_id')
            ->with('pegawai:id,nama')
            ->get();

        return view('dashboard.partials.attendance_stats', compact(
            'leaderboard','month','year'
        ));
    }
}
