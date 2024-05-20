<?php

namespace Database\Seeders;

use App\Models\Permission;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::truncate();
        
        Permission::create([
            'name' => 'GATEWAY_ACCESS',
            'enabled' => 1,
        ]);

        Permission::create([
            'name' => 'COHORT_ACCESS',
            'enabled' => 1,
        ]);

        Permission::create([
            'name' => 'ATLAS_ACCESS',
            'enabled' => 1,
        ]);
    }
}
