<?php

namespace Database\Seeders;

use App\Models\Registry;
use App\Models\Organisation;
use App\Models\RegistryHasOrganisation;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegistrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Registry::truncate();
        
        Registry::factory(1)->create();

        $organisation = Organisation::all();
        RegistryHasOrganisation::create([
            'registry_id' => 1,
            'organisation_id' => $organisation[0]->id,
        ]);
    }
}
