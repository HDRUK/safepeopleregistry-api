<?php

namespace Database\Factories;

use Hash;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Registry>
 */
class RegistryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $signature = Str::random(64);
        $digiIdent = Hash::make(
            $signature.
            ':'.env('REGISTRY_SALT_1').
            ':'.env('REGISTRY_SALT_2')
        );

        return [
            'dl_ident' => 'ABCDE123456A99AA 12',
            'pp_ident' => '123456789',
            'digi_ident' => $digiIdent,
            'verified' => fake()->boolean(),
        ];
    }
}
