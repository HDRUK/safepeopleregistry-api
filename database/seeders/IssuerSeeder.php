<?php

namespace Database\Seeders;

use App\Models\Issuer;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IssuerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        foreach (config('speedi.issuers') as $issuer) {
            Issuer::factory()->create([
                'name' => $issuer['name'],
                'contact_email' => $issuer['contact_email'],
                'enabled' => 1,
            ]);
        }
    }
}
