<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Subsidiary;

class SubsidiaryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subsidiary::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'  => fake()->company(),
            'address_1' => fake()->streetAddress(),
            'address_2' => fake()->secondaryAddress(),
            'town' => fake()->city(),
            'county' => fake()->state(),
            'country' => fake()->country(),
            'postcode' => fake()->postcode(),
            'website' => fake()->url(),
            'is_parent' => fake()->randomElement([0, 1]),
        ];
    }
}
