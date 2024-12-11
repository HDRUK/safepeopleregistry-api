<?php

namespace Database\Factories;

use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'organisation_name' => 'HEALTH DATA RESEARCH UK',
            'address_1' => '215 Euston Road',
            'address_2' => null,
            'town' => '',
            'county' => 'London',
            'country' => 'United Kingdom',
            'postcode' => 'NW1 2BE',
            'lead_applicant_organisation_name' => fake()->name(),
            'lead_applicant_email' => fake()->email(),
            'password' => '$2y$12$pceM5s5kiqPGN.Xpbv/dtu2Mfs37JDjVOGyTpZAyux8brdH8XrAHa',
            'organisation_unique_id' => Str::random(40),
            'applicant_names' => fake()->name(),
            'funders_and_sponsors' => fake()->company(),
            'sub_license_arrangements' => fake()->sentence(5),
            'verified' => true,
            'dsptk_ods_code' => Str::random(fake()->randomElement([3, 4, 5])),
            'dsptk_certified' => fake()->randomElement([0, 1]),
            'companies_house_no' => '10887014',
            'iso_27001_certification_num' => Str::random(12),
            'sector_id' => fake()->randomElement([0, count(Sector::SECTORS)]),
            'charity_registration_id' => '1186569',
            'ror_id' => '02wnqcb97',
            'smb_status' => false,
            'website' => 'https://www.nhs.uk/',
        ];
    }
}
