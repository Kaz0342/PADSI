<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Shift;
use App\Models\Jadwal;

class DummySeeder extends Seeder
{
    public function run()
    {
        // Owner
        $owner = User::create([
            'username' => 'rudi',
            'name' => 'Rudi Hermawan',
            'password' => Hash::make('password123'),
            'role' => 'owner'
        ]);

        // Pegawai owner (opsional)
        $p = Pegawai::create([
            'user_id' => $owner->id,
            'nama' => 'Rudi',
            'posisi' => 'Owner',
            'status' => 'aktif'
        ]);

        // Pegawai biasa
        $u = User::create([
            'username' => 'ari',
            'name' => 'Ari Wibowo',
            'password' => Hash::make('barista123'),
            'role' => 'pegawai'
        ]);
        $peg = Pegawai::create([
            'user_id' => $u->id,
            'nama' => 'Ari Wibowo',
            'posisi' => 'Barista',
            'status' => 'aktif'
        ]);

        // shifts
        $sore = Shift::create(['nama'=>'Sore','start_time'=>'16:00:00','end_time'=>'23:00:00']);

        // jadwal: ari masuk hari ini shift sore
        Jadwal::create([
            'pegawai_id' => $peg->id,
            'shift_id' => $sore->id,
            'tanggal' => date('Y-m-d'),
            'keterangan' => 'Jaga sore'
        ]);
    }
}
