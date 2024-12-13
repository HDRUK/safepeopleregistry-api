<?php

namespace Database\Seeders;

use App\Models\Issuer;
use App\Models\IssuerUser;
use App\Models\IssuerUserHasPermission;
use App\Models\Permission;
use Hash;
use DB;
use Illuminate\Database\Seeder;

class IssuerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Issuer::truncate();
        IssuerUser::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach (config('speedi.issuers') as $issuer) {
            $i = Issuer::factory()->create([
                'name' => $issuer['name'],
                'contact_email' => $issuer['contact_email'],
                'enabled' => 1,
                'idvt_required' => fake()->randomElement([0, 1]),
            ]);

            for ($x = 0; $x < fake()->randomElement([1, 3]); $x++) {
                $iu = IssuerUser::factory()->create([
                    'first_name' => fake()->firstname(),
                    'last_name' => fake()->lastname(),
                    'email' => fake()->email(),
                    'password' => Hash::make('t3mpP4ssword!'),
                    'provider' => '',
                    'keycloak_id' => '',
                    'issuer_id' => $i->id,
                ]);

                $arrs = [
                    'ISSUER_ADMIN',
                    'ISSUER_CREATE',
                    'ISSUER_READ',
                    'ISSUER_UPDATE',
                    'ISSUER_KEYCARD_CREATE',
                    'ISSUER_KEYCARD_REVOKE',
                ];

                $perm = Permission::where('name', '=', fake()->randomElement($arrs))->first();
                IssuerUserHasPermission::create([
                    'issuer_user_id' => $iu->id,
                    'permission_id' => $perm->id,
                ]);
            }
        }
    }
}
