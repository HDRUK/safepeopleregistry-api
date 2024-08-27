<?php

namespace Database\Seeders;

use App\Models\Infringement;

use Illuminate\Database\Seeder;

class InfringementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Infringement::truncate();
        Infringement::factory(10)->create();
    }
}
