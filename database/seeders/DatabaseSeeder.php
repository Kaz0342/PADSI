<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Shift; // Pastikan lo punya model Shift

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat Akun Owner (Super Admin)
        $ownerUser = User::create([
            'name' => 'vio',
            'username' => 'owner',
            'password' => Hash::make('password'), // Password standar
            'role' => 'owner',
        ]);

        // Owner nggak wajib punya data di tabel pegawai, tapi kalau mau konsisten:
        Pegawai::create([
            'user_id' => $ownerUser->id,
            'nama' => 'vio',
            'jabatan' => 'owner',
            'status' => 'Aktif',
        ]);

        // 2. Buat Akun Pegawai (Barista)
        $baristaUser = User::create([
            'name' => 'Alur',
            'username' => 'barista1',
            'password' => Hash::make('password'),
            'role' => 'barista', // Sesuai role yang valid
        ]);

        Pegawai::create([
            'user_id' => $baristaUser->id,
            'nama' => 'Alur',
            'jabatan' => 'barista',
            'status' => 'Aktif',
        ]);

        // 3. Buat Akun Pegawai (Kasir)
        $kasirUser = User::create([
            'name' => 'budi',
            'username' => 'kasir1',
            'password' => Hash::make('password'),
            'role' => 'kasir',
        ]);

        Pegawai::create([
            'user_id' => $kasirUser->id,
            'nama' => 'budi',
            'jabatan' => 'kasir',
            'status' => 'Aktif',
        ]);

        // 4. Buat Data Shift (Penting buat Jadwal)
        Shift::create(['nama' => 'Siang', 'start_time' => '13:00:00', 'end_time' => '18:00:00']);
        Shift::create(['nama' => 'Sore', 'start_time' => '18:00:00', 'end_time' => '23:00:00']);
    }
}