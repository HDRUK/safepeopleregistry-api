<?php

namespace Database\Factories;

use Hash;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Issuer>
 */
class IssuerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $signature = Str::random(40);
        $accessKeySignature = Hash::make($signature . 
            ':' . env('ISSUER_SALT_1') .
            ':' . env('ISSUER_SALT_2')
        );

        return [
            'name' => fake()->name(),
            'unique_identifier' => $accessKeySignature,
            'enabled' => fake()->randomElement([0, 1]),
        ];
    }
}
