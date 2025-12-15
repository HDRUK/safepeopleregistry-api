<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feature;

class ChristmasBannerFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Feature::updateOrCreate(
            [
                'name' => 'christmas-banner',
                'scope' => '__laravel_null',
            ],
            [
                'value' => 'false',
                'description' => 'Enable the Christmas banner across the site.',
            ]
        );

        $this->command->newLine();
        $this->command->info('Christmas banner feature seeded successfully!');
        $this->command->newLine();
    }
}
