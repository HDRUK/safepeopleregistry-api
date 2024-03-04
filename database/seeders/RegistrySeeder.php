<?php

namespace Database\Seeders;

use App\Models\Registry;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegistrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Registry::factory(1)->create();
    }
}
