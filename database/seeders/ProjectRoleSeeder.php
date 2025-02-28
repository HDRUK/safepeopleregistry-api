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
        foreach (ProjectRole::PROJECT_ROLES as $role) {
            ProjectRole::create([
                'name' => $role,
            ]);
        }
    }
}
