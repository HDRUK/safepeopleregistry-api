<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BaseProdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Probably need a migrate fresh here to fully clear
        // the database before hand.

        $this->call([
            SectorSeeder::class,
            EntityModelTypeSeeder::class,
            PermissionSeeder::class,
            // CustodianSeeder::class,
            SystemConfigSeeder::class,
            ProjectRoleSeeder::class,
            EmailTemplatesSeeder::class,
            DepartmentSeeder::class,
            WebhookEventTriggerSeeder::class,
        ]);
    }
}
