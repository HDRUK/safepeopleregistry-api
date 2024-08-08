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
            'awarded_at' => $awardedDate->toDateString(),
            'awarding_body_name' => fake()->company(),
            'awarding_body_ror' => fake()->url(),
            'title' => 'Safe Researcher Training',
            'expires_at' => $awardedDate->addYear(2)->toDateString(),
            'awarded_locale' => 'GB',
        ];
    }
}
