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
            AffiliationSeeder::class,
            RegistrySeeder::class,
            IdentitySeeder::class,
            ProjectSeeder::class,
            TrainingSeeder::class,
            HistorySeeder::class,
            EmploymentSeeder::class,
            ExperienceSeeder::class,
            IssuerSeeder::class,
       ]);
    }
}
