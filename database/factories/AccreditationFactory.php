<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accreditation>
 */
class AccreditationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $awardedDate = Carbon::parse(fake()->date());

        return [
            'associated_organisation_name' => fake()->company(),
            'id_string' => fake()->uuid(),
            'issue_date' => $awardedDate->toDateString(),
            'expiry_date' => $awardedDate->addYear(2)->toDateString(),
        ];
    }
}
