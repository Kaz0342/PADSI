<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// MODELS
use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\PosTransactionTemp;

class OwnerDashboardController extends Controller
{
    /* ==========================================================
       KALENDER ABSENSI (JSON)
    ========================================================== */
    public function calendar(Request $request): JsonResponse
    {
        $month = intval($request->month ?? now()->month);
        $year  = intval($request->year ?? now()->year);

        $start = Carbon::create($year, $month, 1)->startOfMonth()->startOfWeek();
        $end   = Carbon::create($year, $month, 1)->endOfMonth()->endOfWeek();

        $absensi = Absensi::whereBetween('tanggal', [$start, $end])
            ->get()
            ->groupBy(fn ($a) => Carbon::parse($a->tanggal)->toDateString());

        $calendar = [];

        $cursor = $start->copy();
        while ($cursor <= $end) {
            $date = $cursor->toDateString();

            $rows = $absensi[$date] ?? collect();

            $calendar[] = [
                'date'      => $date,
                'hadir'     => $rows->where('status_kehadiran', 'hadir')->count(),
                'terlambat' => $rows->where('status_kehadiran', 'terlambat')->count(),
                'alpha'     => $rows->where('status_kehadiran', 'alpha')->count(),
                'isToday'   => $cursor->isToday(),
                'inMonth'   => $cursor->month == $month,
            ];

            $cursor->addDay();
        }

        return response()->json($calendar);
    }

    /* ==========================================================
       STATISTIK BULANAN (JSON / PARTIAL)
    ========================================================== */
    public function stats(Request $request)
    {
        $month = intval($request->month ?? now()->month);
        $year  = intval($request->year ?? now()->year);

        // Leaderboard Kehadiran
        $leaderboard = Absensi::select(
                'pegawai_id',
                DB::raw("COUNT(*) as total")
            )
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->whereIn('status_kehadiran', ['hadir','terlambat'])
            ->groupBy('pegawai_id')
            ->with('pegawai:id,nama')
            ->get()
            ->map(fn ($r) => [
                'nama'  => $r->pegawai->nama ?? 'Unknown',
                'total' => (int) $r->total
            ]);

        // Total Jam Kerja
        $hours = Absensi::select(
                'pegawai_id',
                DB::raw("SUM(TIMESTAMPDIFF(MINUTE, check_in_at, check_out_at)) as minutes")
            )
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->whereNotNull('check_out_at')
            ->groupBy('pegawai_id')
            ->with('pegawai:id,nama')
            ->get()
            ->map(fn ($r) => [
                'nama'  => $r->pegawai->nama ?? 'Unknown',
                'hours' => round(($r->minutes ?? 0) / 60, 2)
            ]);

        return view('dashboard.partials.attendance_stats', compact(
            'leaderboard',
            'hours',
            'month',
            'year'
        ));
    }
}
