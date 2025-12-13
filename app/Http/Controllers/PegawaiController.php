<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;
use App\Models\User;
use App\Models\Cuti; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class PegawaiController extends Controller
{
    public function index()
    {
        // UPDATE: Eager loading user + cutis (diurutkan desc biar dapet yg terbaru)
        // Solusi N+1 Query biar loadingnya nggak kayak siput
        $pegawais = Pegawai::with(['user', 'cutis' => function($q){
            $q->orderBy('id', 'desc'); 
        }])->latest()->get();

        // Hitung manual di PHP biar hemat query DB
        $aktifCount = 0;
        $cutiCount = 0;
        $nonaktifCount = 0;

        foreach ($pegawais as $p) {
            if ($p->status === 'Aktif') $aktifCount++;
            if ($p->status === 'Cuti')  $cutiCount++;
            if ($p->status === 'Nonaktif') $nonaktifCount++;

            // Logic Ambil Alasan Cuti Terbaru
            if ($p->status === 'Cuti') {
                $activeCuti = $p->cutis->first();
                $p->alasan_cuti = $activeCuti ? $activeCuti->alasan : '-';
            } else {
                $p->alasan_cuti = '-';
            }
        }

        return view('pegawai.index', compact('pegawais', 'aktifCount', 'cutiCount', 'nonaktifCount'));
    }

    public function create()
    {
        // Dropdown role buat di view
        $jabatan = ['barista' => 'Barista', 'kasir' => 'Kasir'];

        // Ambil user nganggur
        $usersWithoutPegawai = User::whereNotIn('id', Pegawai::pluck('user_id'))
            ->where('role', '!=', 'owner')
            ->get();

        return view('pegawai.create', compact('jabatan', 'usersWithoutPegawai'));
    }

    // =========================================================================
    // INI LOGIC STORE YANG LO MINTA (Udah gue amanin pake Transaction)
    // =========================================================================
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'username' => 'required|unique:users,username',
            'password' => 'required|min:5',
            'nama'     => 'required',
            'jabatan'  => 'required',
            // Gue tambahin status optional biar gak error, default Aktif nanti
            'status'   => 'nullable|string' 
        ]);

        DB::beginTransaction(); // Mulai transaksi DB biar aman
        try {
            // 2. Buat user baru & role otomatis menjadi 'pegawai'
            // Logic ini sesuai request lo: Role dipaku jadi 'pegawai'
            $user = User::create([
                'username' => $validated['username'],
                'name'     => $validated['nama'],
                'password' => bcrypt($validated['password']), // atau Hash::make()
                'role'     => 'pegawai',   // <-- HARGA MATI SESUAI REQUEST
            ]);

            // 3. Buat record pegawai
            Pegawai::create([
                'user_id' => $user->id,
                'nama'    => $validated['nama'],
                'jabatan' => $validated['jabatan'],
                'status'  => 'Aktif', // Default Aktif sesuai snippet lo
            ]);

            DB::commit(); // Simpan permanen

            return redirect()->back()->with('success', 'Pegawai berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan semua kalau ada error
            return back()->with('error', 'Gagal menambahkan pegawai: ' . $e->getMessage());
        }
    }

    public function edit(Pegawai $pegawai)
    {
        $pegawai->load('user');

        if (request()->wantsJson()) {
            return response()->json([
                'id'       => $pegawai->id,
                'nama'     => $pegawai->nama,
                'jabatan'  => $pegawai->jabatan,
                'status'   => $pegawai->status,
                'user'     => [
                    'id'       => $pegawai->user->id,
                    'username' => $pegawai->user->username,
                    'role'     => $pegawai->user->role
                ]
            ]);
        }

        return abort(404);
    }

    public function update(Request $request, Pegawai $pegawai)
    {
        $validated = $request->validate([
            'nama'        => 'required|string|max:255',
            'username'    => [
                'required','string','max:255',
                Rule::unique('users','username')->ignore($pegawai->user->id)
            ],
            'password'    => 'nullable|string|min:6',
            'jabatan'     => ['required'],   // <-- BENAR, nama input: jabatan
            'status'      => ['required', Rule::in(['Aktif','Cuti','Nonaktif'])],
            'alasan_cuti' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {

            /* ========= UPDATE USER ========= */
            $userData = [
                'name'     => $validated['nama'],
                'username' => $validated['username'],
                'role'     => strtolower($validated['jabatan']), // <-- FIXED
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $pegawai->user->update($userData);

            /* ========= UPDATE PEGAWAI ========= */
            $pegawai->update([
                'nama'    => $validated['nama'],
                'jabatan' => $validated['jabatan'], // <-- FIXED
                'status'  => $validated['status'],
            ]);

            /* ========= LOGIC CUTI ========= */
            if ($validated['status'] === 'Cuti') {

                // Cari cuti aktif
                $existing = Cuti::where('pegawai_id', $pegawai->id)
                        ->whereDate('tanggal_mulai', '<=', now())
                        ->whereDate('tanggal_selesai', '>=', now())
                        ->first();

                if (!$existing) {
                    Cuti::create([
                        'pegawai_id' => $pegawai->id,
                        'tanggal_mulai' => now()->toDateString(),
                        'tanggal_selesai' => now()->addWeek()->toDateString(),
                        'jenis' => 'Cuti',
                        'alasan' => $validated['alasan_cuti'] ?? '-',
                        'status' => 'approved'
                    ]);
                } else {
                    $existing->update([
                        'alasan' => $validated['alasan_cuti'] ?? $existing->alasan
                    ]);
                }

            } else {
                // Set cuti selesai jika status bukan cuti
                Cuti::where('pegawai_id', $pegawai->id)
                    ->whereDate('tanggal_selesai', '>=', now())
                    ->update(['tanggal_selesai' => now()->subDay()->toDateString()]);
            }

            DB::commit();
            return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, Pegawai $pegawai)
    {
        $request->validate([
            'alasan_penghapusan' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            $pegawai->absensis()->delete();
            $pegawai->cutis()->delete();
            $pegawai->user()->delete();
            $pegawai->delete();

            DB::commit();
            return redirect()->route('pegawai.index')->with('success','Pegawai berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error',$e->getMessage());
        }
    }
}