<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;
use App\Models\Jadwal; // Diperlukan untuk cek relasi di destroy

class ShiftController extends Controller
{
    /**
     * Menampilkan daftar shift.
     */
    public function index()
    {
        $shifts = Shift::all();
        // FIX KRUSIAL: Mengubah 'shifts.index' menjadi 'shift.index'
        return view('shift.index', compact('shifts'));
    }

    /**
     * Menyimpan shift baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i', 
        ]);

        try {
            Shift::create([
                'nama' => $request->nama,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            return redirect()->route('shifts.index')->with('success', 'Shift baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan shift: ' . $e->getMessage());
        }
    }

    /**
     * Mengambil data shift untuk modal edit (JSON).
     */
    public function edit(Shift $shift)
    {
        if (request()->wantsJson()) {
            return response()->json($shift);
        }
        return redirect()->route('shifts.index');
    }

    /**
     * Memperbarui data shift.
     */
    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ]);

        try {
            $shift->update([
                'nama' => $request->nama,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            return redirect()->route('shifts.index')->with('success', 'Data shift berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui shift: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus shift.
     */
    public function destroy(Shift $shift)
    {
        try {
            // FIX: Cek apakah shift dipakai di jadwal (Gue asumsikan model Shift punya relasi hasMany Jadwal)
            // Kalau lo belum ada Jadwal::class, tolong tambahin importnya!
            // Lo juga harus memastikan model Shift punya method 'jadwals()'
            if (Jadwal::where('shift_id', $shift->id)->count() > 0) {
                 return back()->with('error', 'Gagal hapus: Shift ini sedang digunakan dalam jadwal karyawan.');
            }
            // Logic dari lo sebelumnya: if ($shift->jadwals()->count() > 0) {
            // Asumsi lo salah karena jadwals() belum di-load, lebih aman pakai query langsung.

            $shift->delete();
            return redirect()->route('shifts.index')->with('success', 'Shift berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus shift: ' . $e->getMessage());
        }
    }
}