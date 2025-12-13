<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya isi hari kerja — tidak membuat user, pegawai, atau shift
        $this->call([
            WorkDaySeeder::class,
        ]);
    }
}
