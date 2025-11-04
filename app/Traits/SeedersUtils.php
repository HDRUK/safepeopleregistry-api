<?php

namespace App\Traits;

use Keycloak;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Registry;
use App\Models\Training;
use App\Models\Education;
use App\Models\Affiliation;
use App\Models\RegistryHasTraining;
use RegistryManagementController as RMC;

trait SeedersUtils
{
    public function createAffiliations(array &$input): void
    {
        foreach ($input as $u) {
            $user = User::where('email', $u['email'])->first();

            if (!isset($u['affiliations'])) {
                continue;
            }

            foreach ($u['affiliations'] as $e) {
                $aff = Affiliation::create([
                    'organisation_id' => $e['organisation_id'],
                    'member_id' => $e['member_id'],
                    'relationship' => $e['relationship'],
                    'from' => $e['from'],
                    'to' => $e['to'],
                    'department' => $e['department'],
                    'role' => $e['role'],
                    'email' => $e['email'],
                    'ror' => $e['ror'],
                    'registry_id' => $user->registry_id,
                ]);
            }
        }

        unset($input);
    }


    public function createUserRegistry(array &$input, bool $legit = true): void
    {
        foreach ($input as $u) {
            $reg = Registry::create([
                'dl_ident' =>       '',
                'pp_ident' =>       '',
                'digi_ident' =>     RMC::generateDigitalIdentifierForRegistry(),
                'verified' =>       0,
            ]);

            $user = User::where('email', $u['email'])->first();

            $user->update([
                'registry_id' => $reg->id,
            ]);

            if (!in_array(env('APP_ENV'), ['testing', 'ci'])) {
                if ($user) {
                    Keycloak::updateSoursdDigitalIdentifier($user);
                }
            }

            if ($user->user_group !== RMC::KC_GROUP_USERS) {
                continue;
            }

            $educations = [];

            // Make up some Education entries for these users
            if ($legit) {
                $educations = [
                    [
                        'title' => 'Infectious Disease \'Omics',
                        'from' => Carbon::now()->subYears(10)->toDateString(),
                        'to' => Carbon::now()->subYears(6)->toDateString(),
                        'institute_name' => 'London School of Hygiene & Tropical Medicine',
                        'institute_address' => 'Keppel Street, London, WC1E 7HT',
                        'institute_identifier' => '00a0jsq62', // ROR
                        'source' => 'user',
                        'registry_id' => $reg->id,
                    ],
                    [
                        'title' => 'MSc Health Data Science',
                        'from' => Carbon::now()->subYears(5)->toDateString(),
                        'to' => Carbon::now()->subYears(4)->toDateString(),
                        'institute_name' => 'University of Exeter',
                        'institute_address' => 'Stocker Road, Exeter, Devon EX4 4SZ',
                        'institute_identifier' => '03yghzc09',
                        'source' => 'user',
                        'registry_id' => $reg->id,
                    ],
                ];
            } else {
                $educations = [
                    [
                        'title' => 'Lobbying and Manipulation tactics for Policy',
                        'from' => Carbon::now()->subYears(10)->toDateString(),
                        'to' => Carbon::now()->subYears(6)->toDateString(),
                        'institute_name' => 'London School of Lobbying',
                        'institute_address' => 'Fake Street, London, WC1E 7HT',
                        'institute_identifier' => '', // ROR
                        'source' => 'user',
                        'registry_id' => $reg->id,
                    ],
                ];
            }

            foreach ($educations as $edu) {
                $ed = Education::create([
                    'title' => $edu['title'],
                    'from' => $edu['from'],
                    'to' => $edu['to'],
                    'institute_name' => $edu['institute_name'],
                    'institute_address' => $edu['institute_address'],
                    'institute_identifier' => $edu['institute_identifier'],
                    'source' => $edu['source'],
                    'registry_id' => $edu['registry_id'],
                ]);
            }

            $trainings = [];

            if ($legit) {
                // Make up some training entries for these users
                $trainings = [
                    [
                        'provider' => 'UK Data Service',
                        'awarded_at' => Carbon::now()->subYears(2)->toDateString(),
                        'expires_at' => Carbon::now()->addYears(3)->toDateString(),
                        'expires_in_years' => 5,
                        'training_name' => 'Safe Researcher Training',
                    ],
                    [
                        'provider' => 'Medical Research Council (MRC)',
                        'awarded_at' => Carbon::now()->subYears(2)->toDateString(),
                        'expires_at' => Carbon::now()->addYears(3)->toDateString(),
                        'expires_in_years' => 5,
                        'training_name' => 'Research, GDPR, and Confidentiality',
                    ],
                ];
            } else {
                $trainings = [
                    [
                        'provider' => 'Office of National Statistics (ONS)',
                        'awarded_at' => Carbon::now()->subYears(10)->toDateString(),
                        'expires_at' => Carbon::now()->subYears(5)->toDateString(),
                        'expires_in_years' => 2,
                        'training_name' => 'Safe Researcher Training',
                    ],
                ];
            }

            foreach ($trainings as $tr) {
                $training = Training::create([
                    'provider' => $tr['provider'],
                    'awarded_at' => $tr['awarded_at'],
                    'expires_at' => $tr['expires_at'],
                    'expires_in_years' => $tr['expires_in_years'],
                    'training_name' => $tr['training_name'],
                ]);

                RegistryHasTraining::create([
                    'registry_id' => $reg->id,
                    'training_id' => $training->id,
                ]);
            }
        }

        unset($input);
        unset($trainings);
        unset($educations);
        unset($reg);
        unset($user);
    }

    public function createUsers(array &$input): void
    {
        foreach ($input as $u) {
            User::create([
                'first_name' =>         $u['first_name'],
                'last_name' =>          $u['last_name'],
                'email' =>              $u['email'],
                'is_org_admin' =>       isset($u['is_org_admin']) ? $u['is_org_admin'] : 0,
                'is_delegate' =>        isset($u['is_delegate']) ? $u['is_delegate'] : 0,
                'user_group' =>         $u['user_group'],
                'organisation_id' =>    isset($u['organisation_id']) ? $u['organisation_id'] : 0,
                'keycloak_id' =>        isset($u['keycloak_id']) ? $u['keycloak_id'] : null,
                'is_sro' =>             isset($u['is_sro']) ? $u['is_sro'] : 0,
            ]);
        }

        unset($input);
    }
}
