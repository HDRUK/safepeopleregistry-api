<?php

namespace Database\Factories;

use Hash;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Custodian>
 */
class CustodianFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $signature = Str::random(40);
        $calculatedHash = Hash::make(
            $signature.
            ':'.env('CUSTODIAN_SALT_1').
            ':'.env('CUSTODIAN_SALT_2')
        );

        return [
            'name' => fake()->name(),
            'unique_identifier' => $signature,
            'calculated_hash' => $calculatedHash,
            'enabled' => fake()->randomElement([0, 1]),
            'idvt_required' => fake()->randomElement([0, 1]),
        ];
    }
}
