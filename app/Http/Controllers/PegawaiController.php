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
        // Mengambil data pegawai dengan relasi User
        $pegawais = Pegawai::with('user')->orderBy('created_at', 'desc')->get(); 

        $aktifCount = 0;
        $cutiCount = 0;
        $nonaktifCount = 0;

        foreach ($pegawais as $pegawai) {
            if ($pegawai->status === 'Aktif') {
                $aktifCount++;
            } elseif ($pegawai->status === 'Nonaktif') {
                $nonaktifCount++;
            } elseif ($pegawai->status === 'Cuti') {
                $cutiCount++;
            }

            // Logic Cuti
            if ($pegawai->status === 'Cuti') {
                $cutiHariIni = Cuti::where('pegawai_id', $pegawai->id)
                                         ->whereDate('tanggal_mulai', '<=', now())
                                         ->whereDate('tanggal_selesai', '>=', now())
                                         ->latest() 
                                         ->first();
                $pegawai->alasan_cuti = $cutiHariIni ? $cutiHariIni->keterangan : 'Cuti Aktif';
            } else {
                $pegawai->alasan_cuti = '-';
            }
        }
        
        return view('pegawai.index', compact('pegawais', 'aktifCount', 'cutiCount', 'nonaktifCount'));
    }

    public function create()
    {
        // Hanya roles 'barista' dan 'kasir' yang bisa ditambahkan dari form ini
        $roles = ['barista' => 'Barista', 'kasir' => 'Kasir'];
        
        // Ambil User yang belum punya data Pegawai dan BUKAN 'owner'
        // Ini digunakan untuk opsi 'link user yang sudah ada' di form create.
        $usersWithoutPegawai = User::whereNotIn('id', Pegawai::pluck('user_id'))
            ->where('role', '!=', 'owner')
            ->get();
        
        return view('pegawai.create', compact('roles', 'usersWithoutPegawai'));
    }

    /**
     * Menyimpan Pegawai baru dengan membuat User baru. (Mode default)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6',
            // Hanya izinkan role yang valid sesuai request
            'role_posisi' => ['required', 'string', Rule::in(['barista', 'kasir'])], 
        ]);

        DB::beginTransaction();
        try {
            // 1. Buat User
            $user = User::create([
                'name' => $validated['nama_lengkap'],
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role_posisi'], // Role user ikut role pegawai
            ]);

            // 2. Buat Pegawai (Status Default: Aktif)
            Pegawai::create([
                'user_id' => $user->id,
                'nama' => $validated['nama_lengkap'],
                'jabatan' => $validated['role_posisi'],
                'status' => 'Aktif', // Default status saat create
            ]);

            DB::commit();
            return redirect()->route('pegawai.index')->with('success', 'Pegawai baru berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan pegawai: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menyimpan Pegawai baru dengan LINKING ke User yang sudah ada. (Ditambahkan berdasarkan permintaan user)
     */
    public function storeLink(Request $request)
    {
        // Validasi berdasarkan blok kode yang diberikan user
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'nama'    => 'required|string|max:255',
            'jabatan' => 'required|string|max:255'
        ]);

        // Verifikasi User yang dipilih
        $user = User::findOrFail($validated['user_id']);

        // Pastikan user belum terhubung ke pegawai mana pun
        if (Pegawai::where('user_id', $user->id)->exists()) {
            return back()->with('error', 'User ini sudah terhubung dengan data Pegawai lain.')->withInput();
        }

        DB::beginTransaction();
        try {
            // Update nama dan role User (user.name diupdate dengan nama Pegawai)
            $user->name = $validated['nama'];
            $user->role = $validated['jabatan']; 
            $user->save();

            // Buat Pegawai baru yang terhubung ke User yang sudah ada
            Pegawai::create([
                'user_id' => $user->id,
                'nama' => $validated['nama'],
                'jabatan' => $validated['jabatan'],
                'status' => 'Aktif',
            ]);

            DB::commit();
            return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil dihubungkan ke User yang ada!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghubungkan User ke Pegawai: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Pegawai $pegawai) 
    {
        $pegawai->load('user'); 
        
        $activeCuti = null;
        if ($pegawai->status === 'Cuti') {
            $activeCuti = Cuti::where('pegawai_id', $pegawai->id)
                                 ->whereDate('tanggal_mulai', '<=', now())
                                 ->whereDate('tanggal_selesai', '>=', now())
                                 ->latest() 
                                 ->first();
        }

        if (request()->wantsJson()) {
            return response()->json([
                'id' => $pegawai->id,
                'nama' => $pegawai->nama,
                'jabatan' => $pegawai->jabatan,
                'status' => $pegawai->status,
                'user' => [
                    'id' => $pegawai->user->id,
                    'username' => $pegawai->user->username,
                    'role' => $pegawai->user->role,
                ],
                'active_cuti' => $activeCuti,
            ]);
        }
        return abort(404);
    }

    public function update(Request $request, Pegawai $pegawai)
    {
        $pegawai->load('user'); 

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($pegawai->user->id)],
            'password_baru' => 'nullable|string|min:6',
            'role_posisi' => ['required', 'string', Rule::in(['owner', 'manager', 'barista', 'kasir'])],
            'status' => ['required', 'string', Rule::in(['Aktif', 'Cuti', 'Nonaktif'])],
            'alasan_cuti' => 'nullable|string|max:1000',
        ]);
        
        DB::beginTransaction();
        try {
            // Update User
            $pegawai->user->name = $validated['nama_lengkap'];
            $pegawai->user->username = $validated['username'];
            $pegawai->user->role = $validated['role_posisi']; 
            if ($validated['password_baru']) {
                $pegawai->user->password = Hash::make($validated['password_baru']);
            }
            $pegawai->user->save();

            // Update Pegawai
            $pegawai->nama = $validated['nama_lengkap'];
            $pegawai->jabatan = $validated['role_posisi'];
            $pegawai->status = $validated['status'];
            $pegawai->save();

            // Logic Cuti (Create/Update/End)
            if ($validated['status'] === 'Cuti') {
                $existingCuti = Cuti::where('pegawai_id', $pegawai->id)
                                         ->whereDate('tanggal_mulai', '<=', now())
                                         ->whereDate('tanggal_selesai', '>=', now())
                                         ->first();
                if (!$existingCuti) {
                    Cuti::create([
                        'pegawai_id' => $pegawai->id,
                        'tanggal_mulai' => now()->toDateString(), 
                        'tanggal_selesai' => now()->addWeek()->toDateString(), // Default 1 minggu
                        'keterangan' => $validated['alasan_cuti'] ?? 'Cuti',
                        'status' => 'Disetujui',
                    ]);
                } else {
                    $existingCuti->keterangan = $validated['alasan_cuti'] ?? 'Cuti';
                    $existingCuti->save();
                }
            } else {
                // Jika status berubah dari Cuti ke Aktif/Nonaktif, akhiri cuti
                Cuti::where('pegawai_id', $pegawai->id)
                    ->whereDate('tanggal_selesai', '>=', now())
                    ->update(['tanggal_selesai' => now()->subDay()->toDateString()]); 
            }

            DB::commit();
            return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui pegawai: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, Pegawai $pegawai)
    {
        $pegawai->load('user'); 

        $request->validate([
            'alasan_penghapusan' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Hapus Relasi (Pastikan Model Pegawai punya relasi ini)
            $pegawai->absensis()->delete(); 
            $pegawai->cutis()->delete();
            $pegawai->user()->delete();
            $pegawai->delete();

            DB::commit();
            return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil dihapus permanen.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus pegawai: ' . $e->getMessage());
        }
    }
}