<?php

namespace Database\Seeders;

use Hash;

use App\Models\User;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Loki Sinclair',
            'email' => 'loki.sinclair@hdruk.ac.uk',
            'password' => Hash::make('tempP4ssword'),
            'registry_id' => 1,
        ]);
    }
}
