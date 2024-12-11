<?php

namespace Database\Seeders;

use DB;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::truncate();

        Project::factory(5)->create();
    }
}
