<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrganisationDelegate>
 */
class OrganisationDelegateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isDpo = fake()->randomElement([true, false]);

        return [
            'first_name' => fake()->firstname(),
            'last_name' => fake()->lastname(),
            'is_dpo' => $isDpo,
            'is_hr' => ($isDpo ? false : true),
            'email' => fake()->email(),
            'priority_order' => 1,
            'organisation_id' => 1,
        ];
    }
}
