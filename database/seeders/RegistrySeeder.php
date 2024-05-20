<?php

namespace Database\Seeders;

use App\Models\Registry;
use App\Models\Affiliation;
use App\Models\RegistryHasAffiliation;

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

        $affiliation = Affiliation::all();
        RegistryHasAffiliation::create([
            'registry_id' => 1,
            'affiliation_id' => $affiliation[0]->id,
        ]);
    }
}
