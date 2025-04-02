<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
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
                'rule_class' => \App\Rules\Users\IdentityVerification::class,
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
                'rule_class' => \App\Rules\Users\UKDataProtection::class,
                'description' => 'A User should be located in a country which adheres to equivalent data protection law.',
                'entity_model_type_id' => 1,
            ],
            [
                'name' => 'Training',
                'model_type' => \App\Models\User::class,
                'conditions' => json_encode([
                    'path' => 'registry.trainings',
                    'expects' => 'NHS Research Secure Data Environment Training',
                ]),
                'rule_class' => \App\Rules\Users\Training::class,
                'description' => 'A User has completed the NHS Research Secure Data Environment training.',
                'entity_model_type_id' => 1,
            ],
            [
                'name' => 'Training',
                'model_type' => \App\Models\User::class,
                'conditions' => json_encode([
                    'path' => 'registry.trainings',
                    'expects' => 'Safe Researcher Training',
                ]),
                'rule_class' => \App\Rules\Users\Training::class,
                'description' => 'A User has completed the ONS Accredited Researcher training.',
                'entity_model_type_id' => 1,
            ],
            [
                'name' => 'Training',
                'model_type' => \App\Models\User::class,
                'conditions' => json_encode([
                    'path' => 'registry.trainings',
                    'expects' => 'Research, GDPR, and Confidentiality',
                ]),
                'rule_class' => \App\Rules\Users\Training::class,
                'description' => 'A User has completed the MRC GDPR training.',
                'entity_model_type_id' => 1,
            ],
            [
                'name' => 'User affiliation',
                'model_type' => \App\Models\User::class,
                'conditions' => json_encode([
                    'path' => 'registry.affiliations',
                    'expects' => [],
                ]),
                'rule_class' => \App\Rules\Users\AffiliatedOrganisation::class,
                'description' => 'A User has been affiliated by a relevant, validated Organisation.',
                'entity_model_type_id' => 1,
            ],
            [
                'name' => 'Data secutiry compliance',
                'model_type' => \App\Models\Organisation::class,
                'conditions' => json_encode([
                    'path' => 'ce_certified',
                    'expects' => 1,
                ]),
                'rule_class' => \App\Rules\Organisations\DataSecurityCompliance::class,
                'description' => 'An Organisation has Cyber Essentials certification.',
                'entity_model_type_id' => 1,
            ],
            [
                'name' => 'Data security compliance',
                'model_type' => \App\Models\Organisation::class,
                'conditions' => json_encode([
                    'path' => 'ce_plus_certified',
                    'expects' => 1,
                ]),
                'rule_class' => \App\Rules\Organisations\DataSecurityCompliance::class,
                'description' => 'An Organisation has Cyber Essentials Plus certification.',
                'entity_model_type_id' => 1,
            ],
            [
                'name' => 'Data security compliance',
                'model_type' => \App\Models\Organisation::class,
                'conditions' => json_encode([
                    'path' => 'iso_27001_certified',
                    'expects' => 1,
                ]),
                'rule_class' => \App\Rules\Organisations\DataSecurityCompliance::class,
                'description' => 'An Organisation has ISO27001 certification.',
                'entity_model_type_id' => 1,
            ],
            [
                'name' => 'Data security compliance',
                'model_type' => \App\Models\Organisation::class,
                'conditions' => json_encode([
                    'path' => 'dsptk_certified',
                    'expects' => 1,
                ]),
                'rule_class' => \App\Rules\Organisations\DataSecurityCompliance::class,
                'description' => 'An Organisation has DSPT certification.',
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
