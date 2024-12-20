<?php

namespace Database\Seeders;

use App\Models\Custodian;
use App\Models\CustodianUser;
use App\Models\CustodianUserHasPermission;
use App\Models\Permission;
use Hash;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class CustodianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        Custodian::truncate();
        CustodianUser::truncate();

        Schema::enableForeignKeyConstraints();

        foreach (config('speedi.custodians') as $custodian) {
            $i = Custodian::factory()->create([
                'name' => $custodian['name'],
                'contact_email' => $custodian['contact_email'],
                'enabled' => 1,
                'idvt_required' => fake()->randomElement([0, 1]),
            ]);

            for ($x = 0; $x < 1; $x++) {
                $iu = CustodianUser::factory()->create([
                    'first_name' => 'Custodian',
                    'last_name' => 'Admin',
                    'email' => 'custodian' . ($x + 1) . '@' . $custodian['name'] . '.notreal',
                    'password' => Hash::make('t3mpP4ssword!'),
                    'provider' => '',
                    'keycloak_id' => '',
                    'custodian_id' => $i->id,
                ]);

                $perm = Permission::where('name', '=', 'CUSTODIAN_ADMIN')->first();
                CustodianUserHasPermission::create([
                    'custodian_user_id' => $iu->id,
                    'permission_id' => $perm->id,
                ]);
            }
        }
    }
}
