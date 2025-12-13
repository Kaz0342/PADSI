<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\TanggalKerja;
use App\Models\PegawaiTanggalKerja;
use App\Models\AbsensiPengganti;
use App\Models\PosTransactionTemp;
use Illuminate\Http\JsonResponse;

// [PENTING] Load Service Shift
use App\Services\ShiftService; 

class AbsensiController extends Controller
{
    /* ============================================================
       HITUNG STATUS KEHADIRAN (Normal Shift)
    ============================================================ */
    protected function determineStatus(Carbon $checkIn, $shiftStart)
    {
        $today = $checkIn->toDateString();
        // Gabungin tanggal hari ini + jam mulai shift dari database
        $start = Carbon::parse("$today $shiftStart");

        $diff = $start->diffInMinutes($checkIn, false);

        if ($diff <= 0) return 'hadir'; // Datang sebelum atau pas jamnya
        if ($diff <= 60) return 'terlambat'; // Telat max 60 menit
        return 'alpha'; // Telat lebih dari sejam
    }


    /* ============================================================
       HALAMAN ABSENSI PEGAWAI
    ============================================================ */
    public function index(Request $request)
    {
        $pegawai = auth()->user()->pegawai;

        if (!$pegawai) {
            return redirect()->route('dashboard')->with('error', 'Akun tidak terhubung ke pegawai.');
        }

        $today = Carbon::today()->toDateString();

        $segments = Absensi::where('pegawai_id', $pegawai->id)
            ->where('tanggal', $today)
            ->orderBy('check_in_at')
            ->get();

        $status = $segments->count() > 0
            ? $segments->first()->status_kehadiran
            : "Belum Check-in";

        return view('absensi.index', compact('segments', 'status', 'pegawai'));
    }


    /* ============================================================
       CHECK-IN — INTEGRASI SHIFT SERVICE
    ============================================================ */
    public function checkIn(Request $request)
    {
        $user    = auth()->user();
        $pegawai = $user->pegawai;
        $today   = Carbon::today()->toDateString();
        $now     = now('Asia/Jakarta');

        /* 1. CEK TOKO TUTUP / OPEN */
        $tgl = TanggalKerja::where('tanggal', $today)->first();

        if (!$tgl || !$tgl->is_open) {
            return back()->with('error', 'Hari ini toko tutup, tidak bisa absen.');
        }

        /* 2. CEK SUDAH ADA SESI AKTIF */
        $active = Absensi::where('pegawai_id', $pegawai->id)
            ->where('tanggal', $today)
            ->whereNull('check_out_at')
            ->first();

        if ($active) {
            return back()->with('error', 'Anda sudah check-in.');
        }

        /* 3. CEK SUDAH SELESAI ABSEN */
        $done = Absensi::where('pegawai_id', $pegawai->id)
            ->where('tanggal', $today)
            ->whereNotNull('check_out_at')
            ->exists();

        if ($done) {
            return back()->with('error', 'Sesi kerja hari ini sudah selesai.');
        }

        /* 4. AMBIL DATA SHIFT PAKE SERVICE */
        $shift = ShiftService::getTodayShiftForPegawai($pegawai->id); 

        /* CASE A — TIDAK PUNYA SHIFT → ABSENSI PENGGANTI */
        if (!$shift) {
            $abs = Absensi::create([
                'pegawai_id' => $pegawai->id,
                'tanggal' => $today,
                'check_in_at' => $now,
                'status_kehadiran' => "pengganti",
                'lokasi_lat' => $request->lat,
                'lokasi_long' => $request->long,
                'location_info' => $request->location_info,
            ]);

            Session::put('absensi_id', $abs->id);

            return redirect()->route('absensi.pengganti.form')
                ->with('info', 'Anda tidak terjadwal hari ini. Pilih siapa yang Anda gantikan.');
        }


        /* CASE B — NORMAL SHIFT (VALIDASI JAM) */
        if (!ShiftService::isNowInShift($shift)) {
            return redirect()->back()->with('error', 'Belum waktunya atau sudah lewat jam shift. Tidak bisa check-in.');
        }

        $shiftStart = $shift->start_time ?? '17:00:00';
        $status = $this->determineStatus($now, $shiftStart);

        Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => $today,
            'check_in_at' => $now,
            'status_kehadiran' => $status,
            'lokasi_lat' => $request->lat,
            'lokasi_long' => $request->long,
            'location_info' => $request->location_info,
        ]);

        return redirect()->route('dashboard')
            ->with('success', "Check-in berhasil. Status: " . ucfirst($status));
    }


    /* ============================================================
       FORM PENGGANTI
    ============================================================ */
    public function showPenggantiForm()
    {
        $absId = Session::get('absensi_id');
        if (!$absId) {
            return redirect()->route('dashboard')->with('error', 'Absensi pengganti tidak ditemukan.');
        }

        $today = Carbon::today()->toDateString();
        $tgl = TanggalKerja::where('tanggal', $today)->first();

        $eligible = Pegawai::whereHas('tanggalKerjas', function ($q) use ($tgl) {
            $q->where('tanggal_kerja_id', $tgl->id);
        })->where('id', '!=', auth()->user()->pegawai->id)->get();

        return view('absensi.pengganti', compact('eligible'));
    }


    /* ============================================================
       SIMPAN PENGGANTI
    ============================================================ */
    public function storePengganti(Request $request)
    {
        $request->validate([
            'menggantikan_id' => 'required|exists:pegawai,id'
        ]);

        $abs = Absensi::findOrFail(Session::get('absensi_id'));

        DB::transaction(function () use ($request, $abs) {

            $log = AbsensiPengganti::create([
                'pengganti_id' => $abs->pegawai_id,
                'digantikan_id' => $request->menggantikan_id,
                'tanggal' => $abs->tanggal,
                'absensi_id' => $abs->id,
            ]);

            $abs->update([
                'status_kehadiran' => 'pengganti',
                'absensi_pengganti_id' => $log->id,
            ]);

        });

        Session::forget('absensi_id');

        return redirect()->route('absensi.index')
            ->with('success', 'Absensi pengganti tersimpan.');
    }


    /* ============================================================
       CHECK-OUT (FIX SHIFT KALONG)
    ============================================================ */
    public function checkOut(Request $request)
    {
        $user = $request->user();

        // 1. Validasi Role
        if ($user->role !== 'pegawai') {
            abort(403, 'Hanya pegawai yang boleh checkout.');
        }

        $pegawai = $user->pegawai;
        if (!$pegawai) return back()->with('error', 'Data pegawai tidak ditemukan.');

        // 2. Cari Absensi Aktif (YG BELUM CHECKOUT)
        // 🔥 UPDATE PENTING: Hapus whereDate('check_in_at', Carbon::today())
        // Biar sesi kemarin yang belum diclose bisa ke-detect.
        $active = Absensi::where('pegawai_id', $pegawai->id)
            ->whereNull('check_out_at') // Cari yg checkoutnya masih kosong
            ->latest() // Ambil yg paling baru
            ->first();

        if (! $active) {
            return back()->with('error', 'Tidak ada sesi absensi aktif (mungkin sudah auto-checkout).');
        }

        // 3. Update Data
        $active->update([
            'check_out_at' => Carbon::now('Asia/Jakarta'),
            'catatan' => $request->alasan ?? null
        ]);

        return back()->with('success', 'Berhasil check-out.');
    }


    /* ============================================================
       OWNER — REKAP
    ============================================================ */
    public function rekap()
    {
        return view('absensi.rekap');
    }

    /* ============================================================
       OWNER — KALENDER JSON
    ============================================================ */
    public function getRekapCalendarJson(Request $req): JsonResponse
    {
        $month = intval($req->month ?? now()->month);
        $year  = intval($req->year ?? now()->year);

        Carbon::setLocale('id');

        $first = Carbon::create($year, $month, 1);
        $days  = $first->daysInMonth;
        $idx   = $first->dayOfWeek;

        $absensi = Absensi::whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->get()
            ->groupBy(fn($a) => Carbon::parse($a->tanggal)->toDateString());

        $posPending = PosTransactionTemp::whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->pluck('tanggal')
            ->map(fn($x) => Carbon::parse($x)->toDateString())
            ->toArray();

        $calendar = [];

        // filler sebelum bulan
        for ($i = 0; $i < $idx; $i++) {
            $date = $first->copy()->subDays($idx - $i);
            $calendar[] = [
                'label' => $date->day,
                'date' => $date->toDateString(),
                'dots' => [],
                'summary' => '',
                'isCurrentMonth' => false,
                'isToday' => false
            ];
        }

        // tanggal bulan ini
        for ($d = 1; $d <= $days; $d++) {
            $dateObj = Carbon::create($year, $month, $d);
            $date = $dateObj->toDateString();

            $dots = [];
            $summary = "0 data";

            if ($absensi->has($date)) {
                $data = $absensi[$date];

                $hadir = $data->whereIn('status_kehadiran', ['hadir', 'pengganti'])->count();
                $summary = "$hadir hadir (" . $data->count() . " sesi)";

                $statuses = $data->pluck('status_kehadiran')->toArray();
                if (in_array('alpha', $statuses)) $dots[] = 'red';
                if (in_array('terlambat', $statuses)) $dots[] = 'yellow';
                if (in_array('hadir', $statuses) || in_array('pengganti', $statuses)) $dots[] = 'green';
            }

            $calendar[] = [
                'label' => $d,
                'date'  => $date,
                'dots'  => array_slice($dots, 0, 3),
                'summary' => $summary,
                'isCurrentMonth' => true,
                'isToday' => Carbon::today('Asia/Jakarta')->isSameDay($dateObj),
                'pos_pending' => in_array($date, $posPending)
            ];
        }

        // filler setelah bulan
        $lastDay = Carbon::create($year, $month, $days);
        $next = $lastDay->copy()->addDay();

        while (count($calendar) % 7 !== 0) {
            $calendar[] = [
                'label' => $next->day,
                'date' => $next->toDateString(),
                'dots' => [],
                'summary' => '',
                'isCurrentMonth' => false,
                'isToday' => false
            ];
            $next->addDay();
        }

        return response()->json([
            'calendar' => $calendar,
            'pos_pending' => $posPending,
            'currentMonthName' => $first->isoFormat('MMMM YYYY'),
            'currentMonth' => $month,
            'currentYear' => $year
        ]);
    }

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

    /* ============================================================
       DETAIL ABSENSI PER TANGGAL
    ============================================================ */
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