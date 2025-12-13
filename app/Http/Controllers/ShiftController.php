<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Pegawai;
use App\Models\TanggalKerja;
use App\Models\Jadwal;
use App\Models\Shift;

class ShiftController extends Controller
{
    /**
     * TAMPILAN JADWAL GANTT MINGGUAN (Senin - Minggu)
     */
    public function index(Request $req)
    {
        Carbon::setLocale('id');

        // 1) auto-create master shift jika kosong (safety)
        if (Shift::count() === 0) {
            Shift::create([
                'nama' => 'Shift Default',
                'start_time' => '16:00:00',
                'end_time' => '23:00:00',
            ]);
        }

        // 2) tentukan minggu yang aktif (monday start)
        $monday = $req->has('week_start')
            ? Carbon::parse($req->week_start)->startOfWeek(Carbon::MONDAY)
            : Carbon::today()->startOfWeek(Carbon::MONDAY);

        // build array tanggal Senin-Minggu
        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $d = $monday->copy()->addDays($i);
            $dates[] = [
                'date' => $d->toDateString(),
                'label' => $d->format('d'),
                'day_name' => $d->isoFormat('dddd'),
            ];
        }

        // 3) pastikan row tanggal_kerja ada untuk tiap tanggal (auto create)
        $dateStrings = array_column($dates, 'date');

        $existing = TanggalKerja::whereIn('tanggal', $dateStrings)
            ->get()
            ->keyBy(fn($t) => $t->tanggal->toDateString());

        $toCreate = [];
        foreach ($dateStrings as $ds) {
            if (!isset($existing[$ds])) {
                $toCreate[] = [
                    'tanggal' => $ds,
                    'day_name' => TanggalKerja::dayNameFromDate($ds),
                    'is_open' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        if (!empty($toCreate)) {
            TanggalKerja::insert($toCreate);
            $existing = TanggalKerja::whereIn('tanggal', $dateStrings)
                ->get()
                ->keyBy(fn($t) => $t->tanggal->toDateString());
        }

        // 4) ambil pegawai + jadwal minggu ini (eager load jadwals filtered)
        $pegawais = Pegawai::with([
            'jadwals' => fn($q) => $q->whereIn('tanggal', $dateStrings)
        ])->orderBy('nama')->get();

        // 5) hitung ringkasan (jumlah shift per pegawai di minggu ini)
        $summary = [];
        foreach ($pegawais as $p) {
            // karena kita eager-load jadwals yang telah difilter, cukup count koleksi
            $summary[$p->id] = $p->jadwals?->count() ?? 0;
        }

        return view('shifts.index', [
            'pegawais' => $pegawais,
            'dates' => $dates,
            'tanggalKerjaRows' => $existing,
            'monday' => $monday,
            'summary' => $summary,
        ]);
    }

    /**
     * TOGGLE JADWAL KERJA (single click)
     */
    public function toggle(Request $req)
    {
        $req->validate([
            'pegawai_id' => 'required|exists:pegawai,id',
            'tanggal' => 'required|date',
        ]);

        $pegawaiId = $req->pegawai_id;
        $tanggal = Carbon::parse($req->tanggal)->toDateString();

        // validasi tanggal buka
        $tgl = TanggalKerja::firstWhere('tanggal', $tanggal);
        if (!$tgl || !$tgl->is_open) {
            return response()->json(['message' => 'Tanggal tutup atau tidak valid'], 422);
        }

        // cek existing jadwal di tabel 'jadwal'
        $existing = Jadwal::where('pegawai_id', $pegawaiId)
            ->where('tanggal', $tanggal)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['status' => 'removed', 'message' => 'Berhasil menghapus jadwal']);
        }

        // ambil shift default yang sudah pasti ada (index auto-create)
        $shiftDefault = Shift::first();
        if (!$shiftDefault) {
            return response()->json(['message' => 'Data master shift kosong'], 500);
        }

        $jadwal = Jadwal::create([
            'pegawai_id' => $pegawaiId,
            'shift_id' => $shiftDefault->id,
            'tanggal' => $tanggal,
            'keterangan' => null,
        ]);

        return response()->json(['status' => 'added', 'message' => 'Berhasil menambah jadwal', 'data' => $jadwal]);
    }

    /**
     * SIMPAN BATCH (save button)
     * Expects payload: { shifts: [ { pegawai_id, tanggal, assign (1|0) }, ... ] }
     */
    public function saveBatch(Request $request)
    {
        $request->validate([
            'shifts' => 'required|array',
            'shifts.*.pegawai_id' => 'required|exists:pegawai,id',
            'shifts.*.tanggal' => 'required|date',
            'shifts.*.assign' => 'required|in:0,1',
        ]);

        $defaultShift = Shift::first();
        if (!$defaultShift) {
            return response()->json(['message' => 'GAGAL: Tabel Master Shift masih kosong.'], 500);
        }

        DB::beginTransaction();
        try {
            foreach ($request->shifts as $s) {
                $pegawaiId = $s['pegawai_id'];
                $tanggal = Carbon::parse($s['tanggal'])->toDateString();
                $assign = intval($s['assign']);

                // Skip if tanggal tutup
                $tglRow = TanggalKerja::firstWhere('tanggal', $tanggal);
                if ($tglRow && !$tglRow->is_open) {
                    // jika toko tutup skip perubahan untuk tanggal ini
                    continue;
                }

                if ($assign === 1) {
                    Jadwal::updateOrCreate(
                        [
                            'pegawai_id' => $pegawaiId,
                            'tanggal' => $tanggal
                        ],
                        [
                            'shift_id' => $defaultShift->id,
                            'keterangan' => null
                        ]
                    );
                } else {
                    Jadwal::where('pegawai_id', $pegawaiId)
                        ->where('tanggal', $tanggal)
                        ->delete();
                }
            }

            DB::commit();
            return response()->json(['message' => 'Jadwal berhasil disimpan!']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * REDIRECT KE MINGGU LAIN
     */
    public function weekJson(Request $req)
    {
        $start = $req->week_start
            ? Carbon::parse($req->week_start)->startOfWeek(Carbon::MONDAY)
            : Carbon::today()->startOfWeek(Carbon::MONDAY);

        return redirect()->route('shifts.index', ['week_start' => $start->toDateString()]);
    }
}
