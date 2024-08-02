<?php

namespace Database\Seeders;

use App\Models\Issuer;
use App\Models\Organisation;
use App\Models\OrganisationHasIssuerApproval;
use App\Models\OrganisationHasIssuerPermission;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class OrganisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Organisation::truncate();

        Organisation::factory(1)->create();

        $org = Organisation::all()[0];

        $issuer = fake()->randomElement(Issuer::all()->select('id'));
        $perms = Permission::where('name', 'ACCESS_GATEWAY')->first();

        OrganisationHasIssuerPermission::create([
            'organisation_id' => $org->id,
            'permission_id' => $perms->id,
            'issuer_id' => $issuer['id'],
        ]);

        OrganisationHasIssuerApproval::create([
            'organisation_id' => $org->id,
            'issuer_id' => $issuer['id'],
        ]);
    }
}
