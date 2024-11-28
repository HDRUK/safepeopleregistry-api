<?php

namespace Database\Seeders;

use Hash;
use Carbon\Carbon;
use App\Models\File;
use App\Models\Issuer;
use App\Models\Permission;
use App\Models\Registry;
use App\Models\RegistryHasFile;
use App\Models\User;
use App\Models\Accreditation;
use App\Models\RegistryHasAccreditation;
use App\Models\UserHasIssuerApproval;
use App\Models\UserHasIssuerPermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

        $signature = Str::random(64);
        $digiIdent = Hash::make(
            $signature.
            ':'.env('REGISTRY_SALT_1').
            ':'.env('REGISTRY_SALT_2')
        );

        $registry = Registry::create([
            'dl_ident' => Str::random(10),
            'pp_ident' => Str::random(10),
            'digi_ident' => $digiIdent,
            'verified' => fake()->boolean(),
        ]);

        $user = User::factory()->create([
            'first_name' => 'Loki',
            'last_name' => 'Sinclair',
            'email' => 'loki.sinclair@hdruk.ac.uk',
            'password' => '$2y$12$cRbUJeY9Yp2G6ghilpJZaeleUivMyqgV0piW2Ao6kEmoPzN9Lxpu.',
            'registry_id' => $registry->id,
            'user_group' => '',
            'orc_id' => '0009-0004-1636-9627',
            'organisation_id' => 1,
            'keycloak_id' => 'd9bd1c86-6640-42e1-bc75-a9b3b4ac1a4d',
        ]);

        $file = File::create([
            'name' => 'doesntexist.doc',
            'type' => 'document',
            'path' => '1234_doesntexist.doc',
            'status' => 'PROCESSED',
        ]);

        RegistryHasFile::create([
            'registry_id' => $registry->id,
            'file_id' => $file->id,
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

        $awardedDate = Carbon::parse(fake()->date());

        $accreditation = Accreditation::create([
            'awarded_at' => $awardedDate->toDateString(),
            'awarding_body_name' => fake()->company(),
            'awarding_body_ror' => fake()->url(),
            'title' => 'Safe Researcher Training',
            'expires_at' => $awardedDate->addYear(2)->toDateString(),
            'awarded_locale' => 'GB',
        ]);
        RegistryHasAccreditation::create([
            'registry_id' => $registry->id,
            'accreditation_id' => $accreditation->id,
        ]);
    }
}
