<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CharityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_id' => $this->faker->unique()->numerify('########'),
            'name' => $this->faker->company(),
            'website' => $this->faker->url(),
            'address_1' => $this->faker->streetAddress(),
            'address_2' => $this->faker->optional()->secondaryAddress(),
            'town' => $this->faker->city(),
            'county' => $this->faker->state(),
            'country' => $this->faker->country(),
            'postcode' => $this->faker->postcode(),
        ];
    }
}
