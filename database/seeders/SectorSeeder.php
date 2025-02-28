<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        Sector::truncate();

        foreach (Sector::SECTORS as $sector) {
            Sector::create([
                'name' => $sector,
            ]);
        }
    }
}
