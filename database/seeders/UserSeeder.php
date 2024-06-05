<?php

namespace Database\Seeders;

use Hash;

use App\Models\User;
use App\Models\Issuer;
use App\Models\Permission;
use App\Models\UserHasIssuerApproval;
use App\Models\UserHasIssuerPermission;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::truncate();
        UserHasIssuerApproval::truncate();
        UserHasIssuerPermission::truncate();

        $user = User::factory()->create([
            'first_name' => 'Loki',
            'last_name' => 'Sinclair',
            'email' => 'loki.sinclair@hdruk.ac.uk',
            'password' => '$2y$12$cRbUJeY9Yp2G6ghilpJZaeleUivMyqgV0piW2Ao6kEmoPzN9Lxpu.',
            'registry_id' => 1,
            'user_group' => '',
        ]);

        $perms = Permission::all();
        $issuers = Issuer::all();

        foreach ($issuers as $i) {
            foreach ($perms as $p) {
                UserHasIssuerPermission::create([
                    'user_id' => $user->id,
                    'issuer_id' => $i->id,
                    'permission_id' => $p->id,
                ]);
            }

            UserHasIssuerApproval::create([
                'user_id' => $user->id,
                'issuer_id' => $i->id,
            ]);
        }

        $user = User::factory()->create([
            'first_name' => 'Peter',
            'last_name' => 'Hammans',
            'email' => 'peter.hammans@hdruk.ac.uk',
            'password' => '$2y$12$02CkVZW3EfoOTvsCMR4b5uhYUH.gOeaBl59MUK90icj336YvuTjAW',
            'registry_id' => 1,
            'user_group' => '',
        ]);

        $perms = Permission::all();
        $issuers = Issuer::all();

        foreach ($issuers as $i) {
            foreach ($perms as $p) {
                UserHasIssuerPermission::create([
                    'user_id' => $user->id,
                    'issuer_id' => $i->id,
                    'permission_id' => $p->id,
                ]);
            }

            UserHasIssuerApproval::create([
                'user_id' => $user->id,
                'issuer_id' => $i->id,
            ]);
        }        
    }
}
