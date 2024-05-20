<?php

namespace Database\Seeders;

use App\Models\Affiliation;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AffiliationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Affiliation::truncate();

        Affiliation::factory(1)->create();
    }
}
