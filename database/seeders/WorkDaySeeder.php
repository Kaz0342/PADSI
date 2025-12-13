<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkDaySeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data tanpa truncate → aman terhadap foreign key
        DB::table('work_days')->delete();

        $days = [
            ['day_name' => 'Senin',  'is_open' => 0],
            ['day_name' => 'Selasa', 'is_open' => 1],
            ['day_name' => 'Rabu',   'is_open' => 1],
            ['day_name' => 'Kamis',  'is_open' => 1],
            ['day_name' => 'Jumat',  'is_open' => 1],
            ['day_name' => 'Sabtu',  'is_open' => 1],
            ['day_name' => 'Minggu', 'is_open' => 1],
        ];

        DB::table('work_days')->insert($days);
    }
}
