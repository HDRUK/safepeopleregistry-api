<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Identity>
 */
class IdentityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registry_id' => 1,
            'selfie_path' => 'storage/1/selfie.jpeg',
            'passport_path' => 'storage/1/passport.jpeg',
            'drivers_license_path' => 'storage/1/drivers_license.jpeg',
            'address_1' => '123 Road',
            'address_2' => null,
            'town' => 'Town',
            'county' => 'County',
            'country' => 'United Kingdom',
            'postcode' => 'AB12 3CD',
            'dob' => '1977-07-25',
            'idvt_result' => null,
            'idvt_result_perc' => null,
            'idvt_errors' => null,
            'idvt_completed_at' => null,
        ];
    }
}
