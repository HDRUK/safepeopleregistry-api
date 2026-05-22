<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class UpdateUserKeycloakInfoDev extends Command
{
    protected $signature = 'users:update-keycloak-dev';
    protected $description = 'Update keycloak_id, t_and_c_agreed, and t_and_c_agreement_date for users';

    public function handle()
    {
        $usersToUpdate = [
        'admin.user@healthdataorganisation.com' => 'f54ea902-03c8-4b76-a8c7-14807e81340b',
        'admin.user@tandyenergyltd.com' => '8cb8bab9-e000-446c-91b3-3c08979b5d34',
        'admin.user@tobaccoeultd.com' => '117c2346-d085-4ac1-bee1-e446583133fb',
        'annie.potts@ghostbusters.com' => '493c44c1-8473-450d-8696-a6f46cea0b5c',
        'bill.murray@ghostbusters.com' => '6a5283ed-5b43-4c6f-9959-eaac33ba6966',
        'custodian1@nhs.england.notreal' => 'bef0d22d-7101-45bb-b226-8d780bb86ce3',
        'custodian1@sail.databank.notreal' => '372a6104-052c-4959-88c5-0a09cbc7fe8b',
        'dan.ackroyd@ghostbusters.com' => '02c60c3a-f73d-4d9b-8156-107f45f3a4d5',
        'delegate.sponsor@healthdataorganisation.com' => 'bcc077ba-39dc-4d7e-b5ac-f5273d40d780',
        'delegate.sponsor@tandyenergyltd.com' => '55b0c637-5855-453a-8af1-aad07099fa89',
        'delegate.sponsor@tobaccoeultd.com' => 'fda8a648-7f08-4e16-981e-c99c7b3151ff',
        'harold.ramis@ghostbusters.com' => 'abcbe80b-6f42-4833-96d7-21c9addb3732',
        'jennifer.runyon@ghostbusters.com' => '262b7d26-d0ce-4dd3-8aae-bc3545b5e346',
        'organisation.owner@healthdataorganisation.com' => 'c1ef9d94-43f2-4251-85a8-6ba26797bf1d',
        'organisation.owner@tandyenergyltd.com' => '501036e0-a819-4143-add9-6e8f5bfe98fe',
        'sigourney.weaver@ghostbusters.com' => 'e404a878-404d-4769-81b6-4737da62e2b2',
        'tobacco.dave@dodgydomain.com' => '3edefc39-176e-4201-bd21-494c38cce697',
        'tobacco.frank@tobaccoeultd.com' => '9c1e9d16-7813-4e10-aa6c-804ccab7ad67',
        'tobacco.john@dodgydomain.com' => 'a94fc0b7-20f1-4ccc-aec2-59cb1135124b',
        'test.user+user@safepeopleregistry.com' => '04f710fd-afcc-414c-9a19-bcfedf54e82e',
        'test.user+organisation@safepeopleregistry.com' => '9263de78-e22a-4669-a1c4-ac3f6b0260a8',
        'test.user+custodian@safepeopleregistry.com' => '8350891d-fe00-42e1-8012-33178261325e',
        'test.user+admin@safepeopleregistry.com' => '2043cd67-9cc1-4075-97ce-4d6a5ab4a1ce',
        ];

        $updated = 0;

        foreach ($usersToUpdate as $email => $keycloakId) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->keycloak_id = $keycloakId;
                $user->t_and_c_agreed = 1;
                $user->t_and_c_agreement_date = Carbon::now();
                $user->save();

                $this->info("Updated: {$email}");
                $updated++;
            } else {
                $this->warn("User not found: {$email}");
            }
        }

        $this->info("Update complete. Total users updated: {$updated}");
    }
}
