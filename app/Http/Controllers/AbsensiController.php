<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Pegawai;
use App\Models\AbsensiPengganti;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) return redirect()->route('login');

        $pegawai = $user->pegawai;
        if (!$pegawai) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
        }

        $today = Carbon::today()->toDateString();

        $jadwal = Jadwal::with('shift')
                    ->where('pegawai_id', $pegawai->id)
                    ->where('tanggal', $today)
                    ->first();

        $segments = Absensi::where('pegawai_id', $pegawai->id)
                           ->whereDate('tanggal', $today)
                           ->orderBy('check_in_at', 'asc')
                           ->get();

        $status = 'Belum Check-in';

        if ($segments->count() > 0) {
            $firstIn = Carbon::parse($segments->first()->check_in_at);

            if ($jadwal && $jadwal->shift) {
                $mulai = Carbon::createFromFormat('Y-m-d H:i:s', $today . ' ' . $jadwal->shift->start_time);
                $diff = $firstIn->diffInMinutes($mulai, false);

                if ($diff <= 0) {
                    $status = 'hadir';
                } elseif ($diff <= 60) {
                    $status = 'terlambat';
                } else {
                    $status = 'alpha';
                }
            } else {
                $status = 'hadir';
            }
        } else {
            if ($jadwal && $jadwal->shift) {
                $mulai = Carbon::createFromFormat('Y-m-d H:i:s', $today . ' ' . $jadwal->shift->start_time);
                if (Carbon::now()->greaterThan($mulai->copy()->addMinutes(60))) {
                    $status = 'alpha';
                }
            }
        }

        return view('absensi.index', compact('segments', 'jadwal', 'status', 'pegawai'));
    }

    public function checkIn(Request $request)
    {
        $user = auth()->user();
        if (!$user) return redirect()->route('login');

        $pegawai = $user->pegawai;
        if (!$pegawai) return back()->with('error', 'Data pegawai tidak ditemukan.');

        $today = Carbon::today()->toDateString();

        $existing = Absensi::where('pegawai_id', $pegawai->id)
                           ->whereDate('tanggal', $today)
                           ->first();
        if ($existing) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Anda sudah melakukan check-in hari ini.'], 422);
            }
            return back()->with('error', 'Anda sudah melakukan check-in hari ini.');
        }

        $abs = Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => $today,
            'check_in_at' => Carbon::now()->toDateTimeString(),
            'lokasi_lat' => $request->input('lat'),
            'lokasi_long' => $request->input('long'),
            'location_info' => $request->input('location_info'),
            'status_kehadiran' => 'hadir',
        ]);

        $jadwal = Jadwal::with('shift')
                    ->where('pegawai_id', $pegawai->id)
                    ->where('tanggal', $today)
                    ->first();

        if ($jadwal && $jadwal->shift) {
            $status = $this->determineStatus($abs->check_in_at, $jadwal->shift->start_time);
            $abs->status_kehadiran = $status;
            $abs->save();
        }

        if (!$jadwal) {
            if ($request->wantsJson()) {
                return response()->json([
                    'need_replacement' => true,
                    'absensi_id' => $abs->id,
                    'message' => 'Anda tidak terjadwal hari ini. Silakan pilih karyawan yang Anda gantikan.'
                ]);
            }

            return redirect()->route('absensi.pengganti.form')->with('info', 'Anda tidak terjadwal hari ini. Silakan pilih karyawan yang Anda gantikan.');
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'absensi_id' => $abs->id]);
        }

        return redirect()->route('absensi.index')->with('success', 'Check-in berhasil.');
    }

    public function showPenggantiForm(Request $request)
    {
        $user = auth()->user();
        if (!$user) return response()->redirectToRoute('login');

        $pegawai = $user->pegawai;
        $karyawan = Pegawai::where('id', '!=', $pegawai->id)->orderBy('nama')->get(['id', 'nama']);

        if ($request->wantsJson()) {
            return response()->json(['karyawan' => $karyawan]);
        }

        return view('absensi.pengganti', ['karyawan' => $karyawan]);
    }

    public function storePengganti(Request $request)
    {
        $user = auth()->user();
        if (!$user) return redirect()->route('login');

        $pegawai = $user->pegawai;
        if (!$pegawai) return back()->with('error', 'Data pegawai tidak ditemukan.');

        $validated = $request->validate([
            'absensi_id' => 'required|exists:absensi,id',
            'digantikan_id' => 'required|exists:pegawai,id',
            'keterangan' => 'nullable|string|max:1000'
        ]);

        $absensi = Absensi::findOrFail($validated['absensi_id']);

        if ($absensi->pegawai_id !== $pegawai->id) {
            return back()->with('error', 'Absensi tidak valid untuk user ini.');
        }

        DB::beginTransaction();
        try {
            $log = AbsensiPengganti::create([
                'pengganti_id' => $pegawai->id,
                'digantikan_id' => $validated['digantikan_id'],
                'tanggal' => $absensi->tanggal,
                'absensi_id' => $absensi->id,
                'keterangan' => $validated['keterangan'] ?? null
            ]);

            $absensi->absensi_pengganti_id = $log->id;
            $absensi->save();

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Pengganti tersimpan', 'log_id' => $log->id]);
            }

            return redirect()->route('absensi.index')->with('success', 'Pengganti tersimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('storePengganti error: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Terjadi kesalahan menyimpan pengganti'], 500);
            }
            return back()->with('error', 'Gagal menyimpan pengganti.');
        }
    }

    public function checkOut(Request $request)
    {
        $user = auth()->user();
        if (!$user) return redirect()->route('login');

        $pegawai = $user->pegawai;
        if (!$pegawai) return back()->with('error', 'Data pegawai tidak ditemukan.');

        $today = Carbon::today()->toDateString();

        $segment = Absensi::where('pegawai_id', $pegawai->id)
                          ->whereNull('check_out_at')
                          ->orderBy('check_in_at', 'desc')
                          ->first();

        if (!$segment) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Tidak ada sesi check-in aktif'], 422);
            }
            return back()->with('error', 'Tidak ada sesi check-in aktif.');
        }

        $jadwal = Jadwal::with('shift')->where('pegawai_id',$pegawai->id)->where('tanggal',$segment->tanggal)->first();

        if ($jadwal && $jadwal->shift) {
            $selesai = Carbon::createFromFormat('Y-m-d H:i:s', $segment->tanggal . ' ' . $jadwal->shift->end_time);
            if (Carbon::now()->lessThan($selesai)) {
                $request->validate(['alasan' => 'required|string|max:1000']);
            }
        }

        $segment->update([
            'check_out_at' => Carbon::now()->toDateTimeString(),
            'catatan' => $request->input('alasan') 
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Check-out berhasil.');
    }

    protected function determineStatus($checkInAt, $shiftStartTime)
    {
        $checkIn = Carbon::parse($checkInAt);
        $today = Carbon::today()->toDateString();
        $start = Carbon::createFromFormat('Y-m-d H:i:s', $today . ' ' . $shiftStartTime);

        $diff = $checkIn->diffInMinutes($start, false);

        if ($diff <= 0) return 'hadir';
        if ($diff <= 60) return 'terlambat';
        return 'alpha';
    }
}
