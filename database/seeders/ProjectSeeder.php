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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Project::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Project::factory(5)->create();
    }
}
