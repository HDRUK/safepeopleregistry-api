<?php

namespace Database\Factories;

use Carbon\Carbon;
use App\Models\ProjectRole;
use App\Traits\CommonFunctions;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Affiliation>
 */
class AffiliationFactory extends Factory
{
    use CommonFunctions;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => 1,
            'member_id' => fake()->uuid(),
            'relationship' => fake()->randomElement([
                'employment',
                'student',
                'honorary_contract',
            ]),
            'from' => Carbon::now()->toDateString(),
            'to' => null,
            'department' => null,
            'role' => fake()->randomElement(ProjectRole::PROJECT_ROLES),
            'email' => fake()->email(),
            'ror' => generateRorID(),
            'registry_id' => 1,
        ];
    }
}
