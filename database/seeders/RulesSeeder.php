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
                'name' => 'Identity',
                'model_type' => \App\Models\User::class,
                'conditions' => json_encode([
                    'path' => 'registry.identity.idvt_result',
                    'expects' => 1,
                ]),
                'rule_class' => \App\Rules\Users\IdentityVerificationRule::class,
                'description' => 'A User has verified their identity via the Identity Verification Technology (IDVT).',
                'entity_model_type_id' => 2,
            ],
            [
                'name' => 'User location',
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
                'description' => 'A User should be located in a country which adheres to equivalent data protection law.',
                'entity_model_type_id' => 1,
            ],
            [
                'name' => 'Training',
                'model_type' => \App\Models\User::class,
                'conditions' => json_encode([
                    'path' => 'registry.trainings',
                    'expects' => [
                        'name' => [ 
                            'Safe Researcher Training',
                            'Research, GDPR, and Confidentiality',
                        ],
                        'provider' => [
                            'UK Data Service',
                            'Medical Research Council (MRC)',
                        ],
                    ],
                ]),
                'rule_class' => \App\Rules\Users\TrainingRule::class,
                'description' => 'A User must have completed mandatory training before requesting data access.',
                'entity_model_type_id' => 1,
            ],
            [
                'name' => 'Data secutiry compliance',
                'model_type' => \App\Models\Organisation::class,
                'conditions' => json_encode([
                    'paths' => [
                        'ce_certified',
                        'ce_plus_certified',
                        'iso_27001_certified',
                    ],
                    'expects' => 1,
                ]),
                'rule_class' => \App\Rules\Users\NHSSDETrainingRule::class,
                'description' => 'A User has completed the NHS Research Secure Data Environment training.',
                'entity_model_type_id' => 1,
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
