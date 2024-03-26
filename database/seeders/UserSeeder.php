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
            'password' => '$2y$10$yRTNhYgk8tW62Nfcy0ctiuFpGKWK53ctQsGdUF58kymM7n00GjW.2',
            'registry_id' => 1,
        ]);
    }
}
