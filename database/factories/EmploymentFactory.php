<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employment>
 */
class EmploymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employer_name' => 'Demo Employer Name',
            'from' => fake()->date(),
            'to' => fake()->date(),
            'is_current' => fake()->randomElement([0, 1]),
            'department' => fake()->sentence(2),
            'role' => fake()->sentence(3),
            'employer_address' => fake()->address(),
            'ror' => fake()->url(),
            'registry_id' => 1,
        ];
    }
}
