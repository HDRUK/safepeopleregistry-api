<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registry_id' => 1,
            'name' => 'My First Project',
            'public_benefit' => 'Prevents people from dying suddenly',
            'runs_to' => '2028-01-01',
            'affiliate_id' => 1,
        ];
    }
}
