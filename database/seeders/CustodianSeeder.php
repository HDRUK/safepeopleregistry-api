<?php

namespace Database\Seeders;

use App\Models\Custodian;
use App\Models\CustodianUser;
use App\Models\CustodianUserHasPermission;
use App\Models\Permission;
use Hash;
use DB;
use Illuminate\Database\Seeder;

class CustodianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Custodian::truncate();
        CustodianUser::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach (config('speedi.custodians') as $custodian) {
            $i = Custodian::factory()->create([
                'name' => $custodian['name'],
                'contact_email' => $custodian['contact_email'],
                'enabled' => 1,
                'idvt_required' => fake()->randomElement([0, 1]),
            ]);

            for ($x = 0; $x < fake()->randomElement([1, 3]); $x++) {
                $iu = CustodianUser::factory()->create([
                    'first_name' => fake()->firstname(),
                    'last_name' => fake()->lastname(),
                    'email' => fake()->email(),
                    'password' => Hash::make('t3mpP4ssword!'),
                    'provider' => '',
                    'keycloak_id' => '',
                    'custodian_id' => $i->id,
                ]);

                $arrs = [
                    'CUSTODIAN_ADMIN',
                    'CUSTODIAN_CREATE',
                    'CUSTODIAN_READ',
                    'CUSTODIAN_UPDATE',
                    'CUSTODIAN_KEYCARD_CREATE',
                    'CUSTODIAN_KEYCARD_REVOKE',
                ];

                $perm = Permission::where('name', '=', fake()->randomElement($arrs))->first();
                CustodianUserHasPermission::create([
                    'custodian_user_id' => $iu->id,
                    'permission_id' => $perm->id,
                ]);
            }
        }
    }
}
