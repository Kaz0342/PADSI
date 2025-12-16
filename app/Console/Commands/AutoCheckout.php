<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Absensi;
use App\Models\Jadwal;

class AutoCheckout extends Command
{
    protected $signature = 'absensi:finalize';
    protected $description = 'Auto Checkout + Auto Alpha (Final Harian)';

    public function handle()
    {
        $tz = 'Asia/Jakarta';
        $now = Carbon::now($tz);

        // HARD RULE
        $cutoff = Carbon::today($tz)->setTime(23, 30);

        if ($now->lessThan($cutoff)) {
            $this->info('⏳ Belum waktunya finalize absensi.');
            return Command::SUCCESS;
        }

        DB::beginTransaction();

        try {
            /* =====================================================
               1️⃣ AUTO CHECKOUT
            ===================================================== */
            $checkoutCount = Absensi::whereNull('check_out_at')
                ->update([
                    'check_out_at' => $cutoff,
                    'catatan' => 'Auto checkout oleh sistem',
                ]);

            /* =====================================================
               2️⃣ AUTO ALPHA
            ===================================================== */
            $today = $cutoff->toDateString();

            $pegawaiTerjadwal = Jadwal::where('tanggal', $today)
                ->pluck('pegawai_id')
                ->unique();

            $pegawaiSudahAbsen = Absensi::where('tanggal', $today)
                ->pluck('pegawai_id')
                ->unique();

            $pegawaiAlpha = $pegawaiTerjadwal->diff($pegawaiSudahAbsen);

            foreach ($pegawaiAlpha as $pegawaiId) {
                Absensi::create([
                    'pegawai_id'       => $pegawaiId,
                    'tanggal'          => $today,
                    'status_kehadiran' => 'alpha',
                    'tipe_sesi'        => 'system',
                    'catatan'          => 'Auto alpha oleh sistem',
                ]);
            }

            DB::commit();

            $this->info("✅ Finalisasi absensi selesai");
            $this->info("↳ Auto Checkout: {$checkoutCount}");
            $this->info("↳ Auto Alpha: " . count($pegawaiAlpha));

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("❌ ERROR: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
