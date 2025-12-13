<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

use Carbon\Carbon;

// Models
use App\Models\Pegawai;
use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\Jadwal;
use App\Models\PosTransactionTemp;

// Service
use App\Services\ShiftService;

class DashboardController extends Controller
{
    /**
     * Dashboard landing (owner / pegawai)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // -------------------------
        // Owner dashboard
        // -------------------------
        if ($user->role === 'owner') {
            $karyawanAktif = Pegawai::where('status', 'Aktif')->count();

            $karyawanCuti = Cuti::whereDate('tanggal_mulai', '<=', Carbon::today())
                                ->whereDate('tanggal_selesai', '>=', Carbon::today())
                                ->count();

            $hadirHariIni = Absensi::where('tanggal', Carbon::today()->toDateString())
                                   ->distinct('pegawai_id')
                                   ->count('pegawai_id');

            $stokRendah = Schema::hasTable('stock_dummy')
                ? DB::table('stock_dummy')->where('qty', '<=', 3)->count()
                : 0;

            return view('dashboard.owner', compact(
                'user', 'karyawanAktif', 'karyawanCuti', 'hadirHariIni', 'stokRendah'
            ));
        }

        // -------------------------
        // Pegawai dashboard
        // -------------------------
        $pegawai = $user->pegawai;
        if (!$pegawai) {
            return redirect()->route('login')->with('error', 'Akun tidak terhubung ke data pegawai.');
        }

        // Ambil sesi aktif (belum checkout)
        $active = Absensi::where('pegawai_id', $pegawai->id)
                    ->whereNull('check_out_at')
                    ->latest()
                    ->first();

        // Ambil shift via service (single source of truth)
        $shift = ShiftService::getShiftForPegawai($pegawai->id, $active);

        // Tentukan base date untuk menampilkan riwayat (important for overnight shift)
        $shiftDateObj = $active
            ? Carbon::parse($active->check_in_at)->startOfDay('Asia/Jakarta')
            : Carbon::today('Asia/Jakarta')->startOfDay();

        // Riwayat sesi berdasarkan shiftDateObj (agar shift malam tetap muncul)
        $hadirSesi = Absensi::where('pegawai_id', $pegawai->id)
            ->whereBetween('check_in_at', [
                $shiftDateObj->copy()->startOfDay(),
                $shiftDateObj->copy()->endOfDay(),
            ])
            ->orderBy('check_in_at', 'asc')
            ->get();

        // Default status vars untuk view
        $mustPengganti = false;
        $canCheckIn = false;
        $statusAbsen = 'Belum Absen';

        // Vars for checkout modal
        $isEarly = false;
        $endTimeFormatted = null;

        // If there's an active session -> user is "on duty"
        if ($active) {
            $statusAbsen = ucfirst($active->status_kehadiran ?? 'hadir');

            // --- [FIX] LOGIKA HITUNG JAM PULANG ---
            // Kita hitung manual disini biar tidak error panggil method service yg tidak ada
            if ($shift) {
                $start = Carbon::parse($shift->start_time, 'Asia/Jakarta');
                $end   = Carbon::parse($shift->end_time, 'Asia/Jakarta');
                
                // Base date ikut tanggal check-in
                $base = Carbon::parse($active->check_in_at)->startOfDay('Asia/Jakarta');

                // Logic shift kalong (nyebrang hari)
                if ($end->lessThan($start)) {
                    $endTime = $base->copy()->addDay()->setTime($end->hour, $end->minute);
                } else {
                    $endTime = $base->copy()->setTime($end->hour, $end->minute);
                }

                $now = Carbon::now('Asia/Jakarta');
                $isEarly = $now->lessThan($endTime);
                $endTimeFormatted = $endTime->format('H:i');
            }
            // -------------------------------------

        } else {
            // No active session -> decide if can check-in or must fill pengganti
            if ($shift) {
                // Use service helper to check "now in shift range"
                $nowInShift = ShiftService::isNowInShift($shift);

                if ($nowInShift) {
                    $canCheckIn = true;
                    $statusAbsen = 'Siap Absen';
                } else {
                    $statusAbsen = 'Diluar Jam Shift';
                }

            } else {
                // Tidak ada jadwal -> wajib pakai form pengganti
                $mustPengganti = true;
                $statusAbsen = 'Tidak Terjadwal';
            }
        }

        // Jika wajib pengganti dan ada sesi pending di session -> redirect ke form
        if ($mustPengganti && ! $active) {
            if (Session::get('absensi_id')) {
                return redirect()->route('absensi.pengganti.form')
                    ->with('info', 'Selesaikan absensi pengganti terlebih dahulu.');
            }
        }

        return view('dashboard.pegawai', [
            'user' => $user,
            'pegawai' => $pegawai,
            'shift' => $shift,
            'active' => $active,
            'mustPengganti' => $mustPengganti,
            'canCheckIn' => $canCheckIn,
            'statusAbsen' => $statusAbsen,
            'hadirSesi' => $hadirSesi,
            'isEarly' => $isEarly,
            'endTimeFormatted' => $endTimeFormatted,
        ]);
    }

    /**
     * API: kalender dynamic utk owner
     */
    public function getCalendarJson(Request $request): JsonResponse
    {
        if (!Auth::check() || Auth::user()->role !== 'owner') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $month = intval($request->query('month', now()->month));
        $year  = intval($request->query('year', now()->year));

        Carbon::setLocale('id');
        $firstDay = Carbon::create($year, $month, 1);

        $startGrid = $firstDay->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $endGrid   = $firstDay->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $absensiData = Absensi::whereBetween('tanggal', [$startGrid->toDateString(), $endGrid->toDateString()])
                ->get()
                ->groupBy(fn($x) => Carbon::parse($x->tanggal)->toDateString());

        $pendingPos = [];
        try {
            $pendingPos = PosTransactionTemp::whereBetween('tanggal', [$startGrid->toDateString(), $endGrid->toDateString()])
                ->pluck('tanggal')
                ->map(fn($x) => Carbon::parse($x)->toDateString())
                ->toArray();
        } catch (\Exception $e) { }

        $days = [];
        $curr = $startGrid->copy();

        while ($curr <= $endGrid) {
            $dateStr = $curr->toDateString();
            $dots = [];
            $summary = "";

            if (isset($absensiData[$dateStr])) {
                $rows = $absensiData[$dateStr];
                $hadirCount = $rows->whereIn('status_kehadiran', ['hadir','pengganti'])->count();
                $summary = $hadirCount > 0 ? "$hadirCount Pegawai" : "";

                $status = $rows->pluck('status_kehadiran')->unique()->toArray();
                if (in_array('alpha',$status)) $dots[]='red';
                if (in_array('terlambat',$status)) $dots[]='yellow';
                if (in_array('hadir',$status) || in_array('pengganti',$status)) $dots[]='green';
            }

            $days[] = [
                'label' => $curr->day,
                'date' => $dateStr,
                'dots' => array_slice(array_unique($dots),0,3),
                'summary' => $summary,
                'isCurrentMonth' => $curr->month == $month,
                'isToday' => $curr->isToday(),
                'pos_pending' => in_array($dateStr,$pendingPos),
            ];

            $curr->addDay();
        }

        return response()->json([
            'calendar' => $days,
            'pos_pending' => $pendingPos,
            'currentMonthName' => $firstDay->isoFormat('MMMM YYYY'),
            'currentMonth' => $month,
            'currentYear' => $year,
        ]);
    }

    /**
     * STATS: Data untuk grafik/statistik owner (INI YANG KAMU MINTA)
     */
    public function stats(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year ?? now()->year;

        $leaderboard = Absensi::select('pegawai_id', DB::raw("COUNT(*) as hadir_count"))
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->whereIn('status_kehadiran', ['hadir','pengganti'])
            ->groupBy('pegawai_id')
            ->with('pegawai:id,nama')
            ->get()
            ->map(fn($r) => [
                'nama' => $r->pegawai->nama ?? 'Unknown',
                'hadir_count' => (int)$r->hadir_count
            ]);

        $hours = Absensi::select('pegawai_id', DB::raw("SUM(TIMESTAMPDIFF(MINUTE, check_in_at, check_out_at)) as total_minutes"))
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->whereNotNull('check_out_at')
            ->groupBy('pegawai_id')
            ->with('pegawai:id,nama')
            ->get()
            ->map(fn($r) => [
                'nama' => $r->pegawai->nama ?? 'Unknown',
                'hours' => round(($r->total_minutes ?? 0) / 60, 2)
            ]);

        return view('dashboard.partials.attendance_stats', compact(
            'month', 'leaderboard', 'hours'
        ));
    }

    /**
     * History pegawai (INI JUGA SAYA KEMBALIKAN BIAR LENGKAP)
     */
    public function history(Request $req)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return redirect()->route('dashboard')->with('error', 'Akun tidak terhubung.');
        }

        $month = intval($req->month ?? now()->month);
        $year  = intval($req->year ?? now()->year);

        $absensi = Absensi::where('pegawai_id', $pegawai->id)
                        ->whereMonth('tanggal', $month)
                        ->whereYear('tanggal', $year)
                        ->orderBy('tanggal','desc')
                        ->get();

        $riwayat = $absensi->map(function($a){
            $durasi = "-";
            if ($a->check_in_at && $a->check_out_at) {
                $m = Carbon::parse($a->check_out_at)->diffInMinutes($a->check_in_at);
                $durasi = floor($m/60)." jam ".($m%60)." menit";
            }
            return [
                'tanggal' => Carbon::parse($a->tanggal)->isoFormat('dddd, D MMMM Y'),
                'check_in' => $a->check_in_at ? Carbon::parse($a->check_in_at)->format('H:i') : '-',
                'check_out' => $a->check_out_at ? Carbon::parse($a->check_out_at)->format('H:i') : '-',
                'durasi' => $durasi,
                'status' => ucfirst($a->status_kehadiran),
                'catatan' => $a->catatan,
            ];
        });

        $totalMinutes = $absensi->sum(function($a){
            if ($a->check_in_at && $a->check_out_at) {
                return Carbon::parse($a->check_out_at)->diffInMinutes($a->check_in_at);
            }
            return 0;
        });

        $summaryJam = floor($totalMinutes / 60)." jam ".($totalMinutes % 60)." menit";

        return view('pegawai.history', compact(
            'pegawai','riwayat','month','year','summaryJam'
        ));
    }

    /**
     * API: Detail absensi per tanggal untuk owner
     */
    public function getRekapDetailJson(Request $req): JsonResponse
    {
        $req->validate(['date'=>'required|date']);
        $date = $req->date;

        $list = Absensi::with('pegawai')
            ->where('tanggal', $date)
            ->get();

        $summary = [
            'hadir' => 0, 'terlambat' => 0, 'alpha' => 0, 'pengganti' => 0
        ];

        $rows = $list->map(function ($a) use (&$summary) {
            $st = strtolower($a->status_kehadiran);
            if (isset($summary[$st])) $summary[$st]++;

            return [
                'nama' => $a->pegawai->nama ?? '-',
                'posisi' => $a->pegawai->jabatan ?? '-',
                'status_kehadiran' => $st,
                'check_in'  => $a->check_in_at ? Carbon::parse($a->check_in_at)->format('H:i') : null,
                'check_out' => $a->check_out_at ? Carbon::parse($a->check_out_at)->format('H:i') : null,
                'catatan'   => $a->catatan
            ];
        });

        return response()->json([
            'date_formatted' => Carbon::parse($date)->isoFormat('dddd, D MMMM YYYY'),
            'summary' => $summary,
            'rows' => $rows
        ]);
    }
}