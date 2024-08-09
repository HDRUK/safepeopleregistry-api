<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SystemConfigSeeder::class,
            IDVTPluginSeeder::class,
            PermissionSeeder::class,
            IssuerSeeder::class,
            OrganisationSeeder::class,
            OrganisationDelegateSeeder::class,
            IdentitySeeder::class,
            ProjectSeeder::class,
            TrainingSeeder::class,
            HistorySeeder::class,
            EmploymentSeeder::class,
            ExperienceSeeder::class,
            EmailTemplatesSeeder::class,
            AccreditationSeeder::class,
            UserSeeder::class,
            InfringementSeeder::class,
            ResolutionSeeder::class,
        ]);
    }
}
