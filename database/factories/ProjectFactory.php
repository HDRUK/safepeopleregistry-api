<?php

namespace Database\Factories;

use \Carbon\Carbon;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unique_id' => Str::random(40),
            'title' => fake()->sentence(),
            'lay_summary' => fake()->sentence(10),
            'public_benefit' => fake()->sentence(20),
            'request_category_type' => fake()->sentence(5),
            'technical_summary' => fake()->sentence(15),
            'other_approval_committees' => fake()->sentence(6),
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(6),
            'affiliate_id' => 1,
        ];
    }
}
