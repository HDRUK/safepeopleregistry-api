<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Affiliation>
 */
class AffiliationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'address_1' => '234 Road',
            'address_2' => null,
            'town' => 'Town',
            'county' => 'County',
            'country' => 'Country',
            'postcode' => 'AB12 3CD',
            'delegate' => fake()->name(),
            'verified' => true,
        ];
    }
}
