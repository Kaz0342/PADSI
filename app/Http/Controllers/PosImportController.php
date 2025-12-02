<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PosTransactionTemp;
use App\Models\Pegawai;
use App\Models\Absensi;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use DB;

class PosImportController extends Controller
{
    public function showUpload()
    {
        return view('import.csv');
    }

    /**
     * HANDLE UPLOAD CSV (POST)
     * - Simpan file di storage/private/tmp
     * - Jika ada row dengan kolom wajib kosong -> REJECT (422)
     * - Parse tanggal/jam robust
     */
    public function upload(Request $req)
    {
        $req->validate([
            'csv' => 'required|file|mimes:csv,txt'
        ]);

        $file = $req->file('csv');

        if (!$file->isValid()) {
            return response()->json(['ok' => false, 'error' => 'Upload invalid'], 500);
        }

        $real = $file->getRealPath();
        $lines = file($real, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines || count($lines) < 2) {
            return response()->json(['ok' => false, 'error' => "CSV kosong atau header hilang"], 422);
        }

        // store file ke tmp
        $storedName = 'pos_import_' . time() . '_' . preg_replace('/[^a-z0-9\.\-_]/i','_', $file->getClientOriginalName());
        $file->storeAs('private/tmp', $storedName);

        // parse
        $rows = array_map(fn($l) => str_getcsv($l), $lines);
        $rawHeader = array_shift($rows);
        $header = [];
        foreach ($rawHeader as $h) {
            $h = trim($h);
            $h = preg_replace('/\x{FEFF}/u', '', $h);
            $h = strtolower($h);
            $header[] = $h;
        }

        DB::beginTransaction();
        try {
            $rowNumber = 1;
            foreach ($rows as $r) {
                $rowNumber++;
                $r = array_map(function($x) {
                    $x = trim($x);
                    $x = preg_replace('/\x{FEFF}/u', '', $x);
                    return $x === '' ? null : $x;
                }, $r);

                while (count($r) < count($header)) $r[] = null;
                $r = array_slice($r, 0, count($header));

                $row = @array_combine($header, $r);
                if (!$row) {
                    DB::rollBack();
                    return response()->json(['ok' => false, 'error' => "Format CSV tidak sesuai (baris $rowNumber)"], 422);
                }

                $kasir = $row['nama kasir'] ?? null;
                $tanggalRaw = trim($row['tanggal'] ?? '');
                $jamRaw = trim($row['jam'] ?? '');

                // jika ada satu baris yang kosong di kolom wajib → REJECT seluruh file
                if (!$kasir || !$tanggalRaw || !$jamRaw) {
                    DB::rollBack();
                    return response()->json(['ok' => false, 'error' => "CSV tidak valid. Kolom wajib kosong pada baris $rowNumber"], 422);
                }

                // normalisasi tanggal: bisa DD/MM/YYYY atau DDMMYYYY
                $tanggalClean = preg_replace('/[^0-9]/', '', $tanggalRaw);

                if (strlen($tanggalClean) === 8) {
                    $tanggalNormalized = substr($tanggalClean,0,2).'/'.substr($tanggalClean,2,2).'/'.substr($tanggalClean,4,4);
                } elseif (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $tanggalRaw)) {
                    $tanggalNormalized = $tanggalRaw;
                } else {
                    DB::rollBack();
                    return response()->json(['ok' => false, 'error' => "Format tanggal tidak dikenali pada baris $rowNumber: '$tanggalRaw'"], 422);
                }

                // normalisasi jam
                if (preg_match('/^\d+:\d+$/', $jamRaw)) $jamRaw .= ':00';
                if (!preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $jamRaw)) {
                    DB::rollBack();
                    return response()->json(['ok' => false, 'error' => "Format jam tidak dikenali pada baris $rowNumber: '$jamRaw'"], 422);
                }

                // parse datetime
                try {
                    $dt = Carbon::createFromFormat('d/m/Y H:i:s', "$tanggalNormalized $jamRaw");
                } catch (\Throwable $e) {
                    DB::rollBack();
                    return response()->json(['ok' => false, 'error' => "Gagal parse tanggal/jam pada baris $rowNumber"], 422);
                }

                PosTransactionTemp::create([
                    'nama_pegawai_pos' => $kasir,
                    'nama_normalized'  => $this->normalizeName($kasir),
                    'timestamp'        => $dt,
                    'tanggal'          => $dt->toDateString(),
                    'kedai'            => $row['nama outlet'] ?? null,
                    'total'            => 0,
                    'source_file'      => $storedName,
                ]);
            }

            DB::commit();

            // cleanup file tmp (jaga 5 file terakhir)
            $this->cleanupCsvTemp();

            return response()->json(['ok' => true]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("UPLOAD CSV ERROR", ['msg' => $e->getMessage()]);
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function normalizeName($s)
    {
        $s = mb_strtolower($s);
        $s = preg_replace('/[^\p{L}\p{N}\s]/u', '', $s);
        $s = preg_replace('/\s+/', ' ', trim($s));
        return $s;
    }

    public function showVerificationPage($date)
    {
        $pegawai = Pegawai::all()->map(function($p) {
            return [
                'id' => $p->id,
                'nama' => $p->nama,
                'nama_norm' => $this->normalizeName($p->nama),
                'shift' => $p->jam_masuk ?? '17:00:00'
            ];
        });

        $pos = PosTransactionTemp::where('tanggal', $date)
                ->orderBy('timestamp', 'asc')
                ->get()
                ->groupBy('nama_normalized');

        $rows = [];
        foreach ($pegawai as $p) {
            $found = $pos[$p['nama_norm']] ?? null;
            $first = $found ? $found->first()->timestamp : null;

            $rows[] = [
                'id' => $p['id'],
                'nama' => $p['nama'],
                'shift' => $p['shift'],
                'first_ts' => $first,
                'has_pos' => (bool)$first,
                'status' => $first ? $this->determineStatusFromTransaction($first) : 'no-pos'
            ];
        }

        return view('absensi.verify-pos', compact('date','rows'));
    }

    public function getVerificationByDate(Request $req)
    {
        $req->validate(['date' => 'required|date']);
        $date = $req->date;

        $pegawai = Pegawai::all()->map(function($p){
            return [
                'id' => $p->id,
                'nama' => $p->nama,
                'nama_norm' => $this->normalizeName($p->nama),
                'jabatan' => $p->jabatan ?? ''
            ];
        });

        $pos = PosTransactionTemp::where('tanggal', $date)
               ->orderBy('timestamp','asc')
               ->get()
               ->groupBy('nama_normalized');

        $rows = [];
        foreach ($pegawai as $p) {
            $found = $pos[$p['nama_norm']] ?? null;
            $firstTs = $found ? $found->first()->timestamp : null;

            $rows[] = [
                'pegawai_id' => $p['id'],
                'nama' => $p['nama'],
                'pos_first_ts' => $firstTs ? $firstTs->toDateTimeString() : null,
                'status_auto' => $this->determineStatusFromTransaction($firstTs),
                'has_pos' => (bool)$firstTs
            ];
        }

        $posOnly = [];
        foreach ($pos as $key => $group) {
            $match = collect($pegawai)->firstWhere('nama_norm',$key);
            if (!$match) {
                $posOnly[] = [
                    'nama_pos' => $group->first()->nama_pegawai_pos,
                    'first_ts' => $group->first()->timestamp->toDateTimeString()
                ];
            }
        }

        return response()->json(['rows'=>$rows,'pos_only'=>$posOnly]);
    }

    private function determineStatusFromTransaction($timestamp)
    {
        if (!$timestamp) return 'no-pos';
        $ts = Carbon::parse($timestamp);
        $open = Carbon::parse($ts->toDateString() . ' 17:00:00');

        if ($ts->lte($open)) return 'hadir';
        if ($ts->lte($open->copy()->addHour())) return 'terlambat';
        return 'alpha';
    }

    public function approveSingle(Request $req)
    {
        $req->validate([
            'pegawai_id' => 'required|int',
            'date' => 'required|date',
            'use_pos_ts' => 'nullable|boolean'
        ]);

        $pegawai = Pegawai::findOrFail($req->pegawai_id);
        $date = $req->date;
        $usePos = $req->use_pos_ts ?? true;

        $norm = $this->normalizeName($pegawai->nama);

        $pos = PosTransactionTemp::where('tanggal',$date)
                ->where('nama_normalized',$norm)
                ->orderBy('timestamp','asc')
                ->first();

        if ($pos && $usePos) {
            $checkInAt = $pos->timestamp;
            $status = $this->determineStatusFromTransaction($checkInAt);
        } else {
            $checkInAt = Carbon::parse($date.' 17:00:00')->toDateTimeString();
            $status = 'terlambat';
        }

        Absensi::updateOrCreate(
            ['pegawai_id'=>$pegawai->id,'tanggal'=>$date],
            ['check_in_at'=>$checkInAt,'status_kehadiran'=>$status]
        );

        // jika tidak ada lagi pos untuk tanggal ini => hapus semua record untuk tanggal itu
        $sisa = PosTransactionTemp::where('tanggal',$date)
                ->where('nama_normalized','!=',$norm)
                ->exists();

        if (!$sisa) {
            PosTransactionTemp::where('tanggal',$date)->delete();
        }

        return response()->json(['ok'=>true]);
    }

    public function approveAllForDate(Request $req)
    {
        $req->validate(['date' => 'required|date']);
        $date = $req->date;

        $pos = PosTransactionTemp::where('tanggal', $date)->get()->groupBy('nama_normalized');

        foreach ($pos as $key => $group) {
            $namaPos = $group->first()->nama_pegawai_pos;
            $pegawai = Pegawai::whereRaw("LOWER(REPLACE(nama,' ',' ')) = ?", [
                $this->normalizeName($namaPos)
            ])->first();

            if (!$pegawai) continue;

            $first = $group->sortBy('timestamp')->first();

            Absensi::updateOrCreate(
                ['pegawai_id' => $pegawai->id, 'tanggal' => $date],
                [
                    'check_in_at' => $first->timestamp,
                    'status_kehadiran' => $this->determineStatusFromTransaction($first->timestamp)
                ]
            );
        }

        PosTransactionTemp::where('tanggal', $date)->delete();

        return response()->json(['ok' => true]);
    }

    private function cleanupCsvTemp()
    {
        $files = Storage::files('private/tmp');
        usort($files, function($a,$b){
            return Storage::lastModified($a) <=> Storage::lastModified($b);
        });
        while (count($files) > 5) {
            $old = array_shift($files);
            Storage::delete($old);
        }
    }
}
