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

        $defaultSystemPermissions = [
            [
                // Generic Access to the Gateway - default to always enabled. Arguably we could remove
                // this, but we keep for completeness and in case of backtracking.
                'name' => 'ACCESS_GATEWAY',
                'enabled' => 1,
            ],
            [
                // Generic Access to the Cohort Discovery tool - default to always off as this
                // is safeguarded.
                'name' => 'ACCESS_COHORT',
                'enabled' => 1,
            ],
            [
                // Generic Access to the Desease Atlas - default to always on
                'name' => 'ACCESS_ATLAS',
                'enabled' => 1,
            ],
            [
                // Combined with SDE Network High and Detailed feasibility - default to 
                // always on
                'name' => 'GATEWAY_SDE_METADATA_HIGH',
                'enabled' => 1,
            ],
            [
                // Combined with SDE Network High and Detailed feasibility - default to
                // always off unless set
                'name' => 'GATEWAY_SDE_METADATA_DETAILED',
                'enabled' => 1,
            ],
            [
                // SDE Network Central Concierge Service - detailed feasibility - default to 
                // always off unless set
                'name' => 'GATEWAY_SDE_NETWORK_CCS',
                'enabled' => 1,
            ],
            [
                // SDE Network - Individual Request Management (workflow/tooling) - default to
                // alwways off unless set
                'name' => 'GATEWAY_SDE_NETWORK_IRM',
                'enabled' => 1,
            ],
            [
                // SDE Network - Research Environment - default to always off, unless set
                'name' => 'GATEWAY_SDE_NETWORK_RE',
                'enabled' => 1,
            ],
            [
                // Detailed Access to Cohort Discovery and allows queries to be run - default on, when access is on
                'name' => 'COHORT_QUERY',
                'enabled' => 1,
            ],
        ];

        foreach ($defaultSystemPermissions as $perm) {
            Permission::create([
                'name' => $perm['name'],
                'enabled' => $perm['enabled'],
            ]);
        }
    }
}
