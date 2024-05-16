<?php

namespace Database\Factories;

use Illuminate\Support\Str;

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
            'organisation_name' => fake()->name(),
            'address_1' => '234 Road',
            'address_2' => null,
            'town' => 'Town',
            'county' => 'County',
            'country' => 'Country',
            'postcode' => 'AB12 3CD',
            'lead_applicant_organisation_name' => fake()->name(),
            'organisation_unique_id' => Str::random(40),
            'applicant_names' => fake()->name(),
            'funders_and_sponsors' => fake()->company(),
            'sub_license_arrangements' => fake()->sentence(5),
            'verified' => true,
            'dsptk_ods_code' => Str::random(fake()->randomElement([3, 4, 5])),
        ];
    }
}
