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
        // It should be noted that Endorsements, Infringements and Resolutions
        // are always visible as part of the default History for a Researcher.
        // The individual components can be flagged as private on a per individual
        // basis. In terms of payload, the specific parts will still be returned
        // but labeled as "private" instead.
        //
        // Future scope would be a mechanism for Data Providers to request
        // access to specific items to back up data access request submissions.
        $perms = [
            'READ_REGISTRY',
            'READ_TRAINING',
            'READ_IDENTITY',
            'READ_AFFILIATION',
            'READ_PROJECT',
            'READ_EXPERIENCE',
            'READ_EMPLOYMENT',
            'READ_HISTORY',
        ];

        foreach ($perms as $p) {
            $perm = Permission::create([
                'name' => $p,
                'enabled' => 1,
            ]);
        }
    }
}
