<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\Jadwal;

/**
 * ShiftService
 * Single source of truth untuk mengambil dan mengecek shift.
 */
class ShiftService
{
    /**
     * Ambil shift yang relevan untuk pegawai.
     *
     * Behavior:
     * - Kalau ada $active (Absensi aktif), pakai tanggal check_in_at -> cocokkan jadwal tanggal itu.
     * - Kalau tidak ada $active -> pakai jadwal tanggal hari ini.
     *
     * @param int $pegawaiId
     * @param mixed $active Absensi model atau null
     * @return \App\Models\Shift|null
     */
    public static function getShiftForPegawai(int $pegawaiId, $active = null)
    {
        $tz = 'Asia/Jakarta';

        if ($active) {
            $shiftDate = Carbon::parse($active->check_in_at, $tz)->toDateString();
        } else {
            $shiftDate = Carbon::today($tz)->toDateString();
        }

        $jadwal = Jadwal::where('pegawai_id', $pegawaiId)
            ->where('tanggal', $shiftDate)
            ->with('shift')
            ->first();

        return $jadwal?->shift ?? null;
    }

    /**
     * Cek apakah sekarang berada di dalam rentang jam shift.
     * Mendukung shift overnight (mis. 20:00 -> 04:00).
     *
     * @param \App\Models\Shift|null $shift
     * @return bool
     */
    public static function isNowInShift($shift): bool
    {
        if (!$shift) return false;
        $tz = 'Asia/Jakarta';
        $now = Carbon::now($tz);

        // Gabungkan tanggal hari ini dengan jam shift
        $start = Carbon::parse($now->toDateString() . ' ' . $shift->start_time, $tz);
        $end   = Carbon::parse($now->toDateString() . ' ' . $shift->end_time, $tz);

        // Kalau end <= start -> shift melewati tengah malam -> tambahin 1 hari ke end
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return $now->between($start, $end);
    }

    /**
     * Hitung waktu pulang (DateTime) berdasarkan:
     * - shift (start_time, end_time)
     * - dan tanggal basis (biasanya tanggal check_in_at)
     *
     * Mengembalikan Carbon instance di timezone Asia/Jakarta.
     *
     * @param \App\Models\Shift $shift
     * @param \Carbon\Carbon $baseDate (harus berformat startOfDay pada tanggal shift yang benar)
     * @return \Carbon\Carbon
     */
    public static function computeEndDateTime($shift, $baseDate)
    {
        $tz = 'Asia/Jakarta';
        $start = Carbon::parse($shift->start_time, $tz);
        $end   = Carbon::parse($shift->end_time, $tz);

        if ($end->lessThanOrEqualTo($start)) {
            // shift melewati tengah malam -> end besok
            return $baseDate->copy()->addDay()->setTime($end->hour, $end->minute)->timezone($tz);
        }

        return $baseDate->copy()->setTime($end->hour, $end->minute)->timezone($tz);
    }
}
