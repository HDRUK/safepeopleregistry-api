<?php

namespace Database\Seeders;

use App\Models\ProfessionalRegistration;
use Illuminate\Database\Seeder;

class ProfessionalRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProfessionalRegistration::truncate();
        ProfessionalRegistration::factory(5)->create();
    }
}
