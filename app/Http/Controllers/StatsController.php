<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Absensi;
use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    /**
     * Halaman/partial stats (bisa dipanggil dari dashboard owner).
     * Jika request wantsJson -> return JSON untuk chart (dipanggil via AJAX).
     */
    public function monthlyAttendance(Request $req)
    {
        // guard: owner only biasanya, tapi asumsi route sudah di-middleware
        $month = intval($req->month ?? now()->month);
        $year  = intval($req->year  ?? now()->year);

        Carbon::setLocale('id');

        // Ambil semua absensi di bulan tersebut
        $absensi = Absensi::with('pegawai')
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->get();

        // 1) Leaderboard: siapa paling sering hadir (hitung unique tanggal dengan status hadir/terlambat/pengganti)
        $presentStatuses = ['hadir', 'terlambat', 'pengganti'];

        // group by pegawai_id -> unique tanggal count
        $byPegawai = $absensi
            ->filter(fn($a) => in_array(strtolower($a->status_kehadiran), $presentStatuses))
            ->groupBy('pegawai_id')
            ->map(function($items, $pegawaiId){
                // count unique tanggal (in case multiple sessions)
                $uniqueDates = $items->groupBy(fn($it) => \Carbon\Carbon::parse($it->tanggal)->toDateString())->count();
                // also get pegawai name
                $pegawaiName = $items->first()->pegawai->nama ?? ('pegawai#' . $pegawaiId);
                return [
                    'pegawai_id' => $pegawaiId,
                    'nama' => $pegawaiName,
                    'hadir_count' => $uniqueDates,
                ];
            })->values();

        // 2) Total jam kerja per pegawai (sum differences check_out - check_in, only records with both)
        $hoursByPegawai = [];
        foreach ($absensi as $a) {
            if ($a->check_in_at && $a->check_out_at) {
                $in  = Carbon::parse($a->check_in_at);
                $out = Carbon::parse($a->check_out_at);
                $minutes = max(0, $out->diffInMinutes($in, false));
                if (!isset($hoursByPegawai[$a->pegawai_id])) {
                    $hoursByPegawai[$a->pegawai_id] = [
                        'pegawai_id' => $a->pegawai_id,
                        'nama' => $a->pegawai->nama ?? ('pegawai#'.$a->pegawai_id),
                        'minutes' => 0
                    ];
                }
                $hoursByPegawai[$a->pegawai_id]['minutes'] += $minutes;
            }
        }
        $hoursByPegawai = collect($hoursByPegawai)->values();

        // Prepare sorted arrays for charts
        $leaderboard = $byPegawai->sortByDesc('hadir_count')->values();
        $hoursSorted = $hoursByPegawai->sortByDesc('minutes')->values();

        // If JSON requested (AJAX) -> return data
        if ($req->wantsJson() || $req->ajax()) {
            return response()->json([
                'month' => $month,
                'year' => $year,
                'leaderboard' => $leaderboard,
                'hours' => $hoursSorted->map(fn($r) => [
                    'pegawai_id' => $r['pegawai_id'],
                    'nama' => $r['nama'],
                    'hours' => round($r['minutes'] / 60, 2),
                    'minutes' => $r['minutes']
                ]),
            ]);
        }

        // Otherwise return blade partial (server-side render)
        return view('dashboard.partials.attendance_stats', [
            'month' => $month,
            'year'  => $year,
            'leaderboard' => $leaderboard,
            'hours' => $hoursSorted->map(fn($r) => [
                'pegawai_id' => $r['pegawai_id'],
                'nama' => $r['nama'],
                'hours' => round($r['minutes'] / 60, 2),
                'minutes' => $r['minutes']
            ]),
        ]);
    }
}
