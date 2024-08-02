<?php

namespace Database\Seeders;

use App\Models\Organisation;
use App\Models\OrganisationDelegate;
use Illuminate\Database\Seeder;

class OrganisationDelegateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OrganisationDelegate::truncate();

        $orgs = Organisation::all();
        $numToCreate = fake()->numberBetween(1, 5);

        foreach ($orgs as $o) {
            for ($i = 0; $i < $numToCreate; $i++) {
                OrganisationDelegate::factory()->create([
                    'priority_order' => $i,
                    'organisation_id' => $o->id,
                ]);
            }
        }
    }
}
