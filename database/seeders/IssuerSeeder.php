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
        Issuer::factory(5)->create();
    }
}
