<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resolution>
 */
class ResolutionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'comment' => fake()->sentence(5),
            'custodian_by' => 1,
            'registry_id' => 1,
            'resolved' => fake()->randomElement([0, 1]),
        ];
    }
}
