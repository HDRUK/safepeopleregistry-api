<?php

namespace Database\Seeders;

use App\Models\State;
use App\Models\Status;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        Project::truncate();

        Schema::enableForeignKeyConstraints();
        Project::factory(5)->create();

        $projects = Project::all();

        foreach ($projects as $project) {
            $project->setState(State::STATE_PROJECT_PENDING);
        }
    }
}
