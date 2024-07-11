<?php

namespace Database\Factories;

use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organisation>
 */
class OrganisationFactory extends Factory
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
            'lead_applicant_email' => fake()->email(),
            'password' => '$2y$12$pceM5s5kiqPGN.Xpbv/dtu2Mfs37JDjVOGyTpZAyux8brdH8XrAHa',
            'organisation_unique_id' => Str::random(40),
            'applicant_names' => fake()->name(),
            'funders_and_sponsors' => fake()->company(),
            'sub_license_arrangements' => fake()->sentence(5),
            'verified' => true,
            'dsptk_ods_code' => Str::random(fake()->randomElement([3, 4, 5])),
            'companies_house_no' => Str::random(8),
        ];
    }
}
