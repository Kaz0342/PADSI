<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

// MODELS
use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\AbsensiPengganti;

class AbsensiController extends Controller
{
    /* ==========================================================
       INDEX — DISPLAY ONLY (PEGAWAI)
    ========================================================== */
    public function index()
    {
        $pegawai = auth()->user()->pegawai;

        if (!$pegawai) {
            return redirect()->route('dashboard')
                ->with('error', 'Akun tidak terhubung ke pegawai.');
        }

        $today = Carbon::today('Asia/Jakarta')->toDateString();

        $absensi = Absensi::where('pegawai_id', $pegawai->id)
            ->where('tanggal', $today)
            ->orderBy('check_in_at')
            ->get();

        return view('absensi.index', compact('pegawai', 'absensi'));
    }

    /* ==========================================================
       FORM ABSENSI PENGGANTI
    ========================================================== */
    public function showPenggantiForm()
    {
        $absensiId = Session::get('absensi_id');

        if (!$absensiId) {
            return redirect()->route('dashboard')
                ->with('error', 'Tidak ada sesi pengganti.');
        }

        $pegawai = auth()->user()->pegawai;

        $eligible = Pegawai::where('id', '!=', $pegawai->id)->get();

        return view('absensi.pengganti', compact('eligible'));
    }

    /* ==========================================================
       STORE ABSENSI PENGGANTI
    ========================================================== */
    public function storePengganti(Request $request)
    {
        $request->validate([
            'menggantikan_id' => 'required|exists:pegawai,id',
            'status_kehadiran' => 'required|in:hadir,terlambat',
        ]);

        $pegawai = auth()->user()->pegawai;
        if (!$pegawai) {
            return redirect()->route('login');
        }

        $absensiId = Session::get('absensi_id');
        if (!$absensiId) {
            return redirect()->route('dashboard')
                ->with('error', 'Sesi pengganti tidak ditemukan.');
        }

        DB::transaction(function () use ($request, $pegawai, $absensiId) {

            $absensi = Absensi::findOrFail($absensiId);

            $absensi->update([
                'status_kehadiran' => $request->status_kehadiran,
                'tipe_sesi'        => 'pengganti',
            ]);

            AbsensiPengganti::create([
                'absensi_id'     => $absensi->id,
                'pengganti_id'   => $pegawai->id,
                'digantikan_id' => $request->menggantikan_id,
                'tanggal'       => $absensi->tanggal,
            ]);
        });

        Session::forget('absensi_id');

        return redirect()->route('dashboard')
            ->with('success', 'Absensi pengganti berhasil disimpan.');
    }

    public function checkIn(Request $request)
    {
        $pegawai = auth()->user()->pegawai;
        if (!$pegawai) {
            return back()->with('error', 'Pegawai tidak ditemukan');
        }

        $today = Carbon::today('Asia/Jakarta')->toDateString();

        // Cegah double check-in
        if (Absensi::where('pegawai_id', $pegawai->id)->where('tanggal', $today)->exists()) {
            return back()->with('error', 'Sudah absen hari ini');
        }

        Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => $today,
            'check_in_at' => Carbon::now('Asia/Jakarta'),
            'status_kehadiran' => 'hadir',
        ]);

        return redirect()->route('dashboard')->with('success', 'Berhasil check-in');
    }


    /* ==========================================================
       CHECK-OUT MANUAL (PEGAWAI)
    ========================================================== */
    public function checkOut(Request $request)
    {
        $pegawai = auth()->user()->pegawai;

        if (!$pegawai) {
            return back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        $active = Absensi::where('pegawai_id', $pegawai->id)
            ->whereNull('check_out_at')
            ->latest()
            ->first();

        if (!$active) {
            return back()->with('error', 'Tidak ada sesi aktif.');
        }

        $active->update([
            'check_out_at' => Carbon::now('Asia/Jakarta'),
            'catatan'      => $request->alasan,
        ]);

        return back()->with('success', 'Berhasil check-out.');
    }

    /* ==========================================================
       OWNER — REKAP
    ========================================================== */
    public function rekap()
    {
        return view('absensi.rekap');
    }
}
