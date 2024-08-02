<?php

namespace Database\Seeders;

use App\Models\Employment;
use Illuminate\Database\Seeder;

class EmploymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Employment::truncate();

        Employment::factory(1)->create();
    }
}
