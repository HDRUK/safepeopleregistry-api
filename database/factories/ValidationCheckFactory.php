<?php

namespace Database\Factories;

use App\Models\ProjectHasUser;
use App\Models\ValidationCheck;
use Illuminate\Database\Eloquent\Factories\Factory;

class ValidationCheckFactory extends Factory
{
    protected $model = ValidationCheck::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->slug,
            'description' => $this->faker->sentence,
            'applies_to' => ProjectHasUser::class,
            'enabled' => 1
        ];
    }
}
