<?php

namespace Database\Seeders;

use App\Models\ProjectRole;
use Illuminate\Database\Seeder;

class ProjectRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProjectRole::create([
            'name' => 'Principal Investigator (PI)',
        ]);

        ProjectRole::create([
            'name' => 'Co-Investigator (Co-I) / Sub-Investigator (Sub-I)',
        ]);

        ProjectRole::create([
            'name' => 'Data Analyst',
        ]);

        ProjectRole::create([
            'name' => 'Data Engineer',
        ]);

        ProjectRole::create([
            'name' => 'Postdoc',
        ]);

        ProjectRole::create([
            'name' => 'Research Fellow',
        ]);

        ProjectRole::create([
            'name' => 'Researcher',
        ]);

        ProjectRole::create([
            'name' => 'Student',
        ]);
    }
}
