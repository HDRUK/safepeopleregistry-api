<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Training>
 */
class TrainingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => fake()->name(),
            'awarded_at' => fake()->date('Y-m-d', '-1 year'),
            'expires_at' => fake()->date(),
            'expires_in_years' => fake()->numberBetween(1, 5),
            'training_name' => fake()->name(),
            'certification_id' => null,
            'pro_registration' => fake()->randomElement([0, 1]),
        ];
    }
}
