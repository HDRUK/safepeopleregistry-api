<?php

namespace Database\Seeders;

use App\Models\Custodian;
use App\Models\Organisation;
use App\Models\OrganisationHasCustodianApproval;
use App\Models\OrganisationHasCustodianPermission;
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

        Organisation::factory(1)->withCharity()->create();

        $org = Organisation::all()[0];

        $custodian = fake()->randomElement(Custodian::all()->select('id'));
        $perms = Permission::where('name', 'ACCESS_GATEWAY')->first();

        OrganisationHasCustodianPermission::create([
            'organisation_id' => $org->id,
            'permission_id' => $perms->id,
            'custodian_id' => $custodian['id'],
        ]);

        OrganisationHasCustodianApproval::create([
            'organisation_id' => $org->id,
            'custodian_id' => $custodian['id'],
        ]);
    }
}
