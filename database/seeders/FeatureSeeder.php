<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feature;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Feature::truncate();

        // Seed features with global scope
        $globalFeatures = [
            ['name' => 'test-feature', 'value' => true, 'description' => 'This is a test feature.'],
            ['name' => 'test-feature-user-admin', 'value' => true, 'description' => 'This feature is enabled for admin users only.'],
        ];

        foreach ($globalFeatures as $feature) {
            Feature::create([
                'name' => $feature['name'],
                'scope' => '__laravel_null',
                'value' => $feature['value'],
                'description' => $feature['description'],
            ]);
        }

        // $scopedFeatures = [
        //     ['name' => 'test-feature-user-1', 'scope' => 'App\\Models\\User:1', 'value' => true, 'description' => 'This feature is enabled for user with ID 1.'],
        // ];

        // foreach ($scopedFeatures as $feature) {
        //     Feature::create([
        //         'name' => $feature['name'],
        //         'scope' => $feature['scope'],
        //         'value' => $feature['value'],
        //         'description' => $feature['description'],
        //     ]);
        // }

        $this->command->newLine();
        $this->command->info('All feature flags seeded successfully!');
        $this->command->newLine();
    }
}
