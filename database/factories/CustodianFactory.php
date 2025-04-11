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
        $uuid = Str::uuid()->toString();
        $calculatedHash = Hash::make(
            $uuid.
            ':'.env('CUSTODIAN_SALT_1').
            ':'.env('CUSTODIAN_SALT_2')
        );

        return [
            'name' => $this->faker->company(),
            'unique_identifier' => $signature,
            'calculated_hash' => $calculatedHash,
            'contact_email' => $this->faker->safeEmail(),
            'enabled' => $this->faker->boolean(),
            'invite_accepted_at' => $this->faker->optional()->dateTimeThisYear(),
            'invite_sent_at' => $this->faker->dateTimeThisYear(),
            'idvt_required' => $this->faker->boolean(),
            'client_id' => $uuid,
        ];
    }
}
