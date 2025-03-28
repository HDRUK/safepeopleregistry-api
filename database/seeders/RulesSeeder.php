<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use App\Models\Rules;
use App\Models\DecisionModel;

class RulesSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        DecisionModel::truncate();

        Schema::enableForeignKeyConstraints();

        $rules = [
            [
                'model_type' => \App\Models\User::class,
                'conditions' => json_encode([
                    'path' => 'registry.identity.idvt_result',
                    'expects' => 1,
                ]),
                'rule_class' => \App\Rules\Users\IdentityVerificationRule::class,
            ],
            [
                'model_type' => \App\Models\User::class,
                'conditions' => json_encode([
                    'path' => 'location',
                    'sanctioned_countries' => [
                        'China',
                        'Russia',
                        'North Korea',
                    ],
                ]),
                'rule_class' => \App\Rules\Users\UKDataProtectionRule::class,
            ],
        ];

        // $rules = [
        //     [
        //         'name' => 'countrySanctions',
        //         'title' => 'Country sanctions',
        //         'description' => 'Users and Organisations who are on UK Sanctions Lists should not be provided access to data (existing demo data).'
        //     ],
        //     [
        //         'name' => 'userLocation',
        //         'title' => 'User location',
        //         'description' => 'A User should be located in a country which adheres to equivalent data protection law.'
        //     ],
        //     [
        //         'name' => 'dueDiligence',
        //         'title' => 'Due Diligence',
        //         'description' => 'Additional organisation due diligence checks should be carried out on Organisations who are not approved Organisations.'
        //     ],
        //     [
        //         'name' => 'training',
        //         'title' => 'Training',
        //         'description' => 'A user must have completed mandatory TRE/SDE training before accessing a TRE/SDE.'
        //     ],
        //     [
        //         'name' => 'dataSecurityCompliance',
        //         'title' => 'Data security compliance',
        //         'description' => 'An organisation must provide data security compliance accreditation information within their profile.'
        //     ],
        //     [
        //         'name' => 'delegate',
        //         'title' => 'Delegate',
        //         'description' => 'An Organisation must have at least one delegate to vouch for a User’s behaviour within a TRE/SDE.'
        //     ]
        // ];

        foreach ($rules as $rule) {
            // Rules::create($rule);
            DecisionModel::create($rule);
        }
    }
}
