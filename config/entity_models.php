<?php

return [
    /*
    |----------------------------------------------------------------------------------------------------
    | Default entity model config
    |----------------------------------------------------------------------------------------------------
    |
    | These options determine the base entity model configuration for SOURSD
    |
    */
    'entities' => [
        'decision_models' => [
            [
                'name' => 'Sanctions',
                'description' => 'Users and Organisations who are on the UK Sanctions List should not be provided with access to sensitive data.',
                'entity_model_type_id' => 1,
                'calls_file' => 1,
                'file_path' => 'sanctions.json',
                'calls_operation' => 0,
                'operation' => null,
            ],
            [
                'name' => 'User location',
                'description' => 'A User should be located in a country which adheres to equivalent data protection law.',
                'entity_model_type_id' => 1,
                'calls_file' => 1,
                'file_path' => 'user_location.json',
                'calls_operation' => 0,
                'operation' => null,
            ],
            [
                'name' => 'Organisation due dilligence',
                'description' => 'Additional Organisation due dilligence checks should be carried our on Organisations who are not validated.',
                'entity_model_type_id' => 1,
                'calls_file' => 1,
                'file_path' => 'organisation_due_dilligence.json',
                'calls_operation' => 0,
                'operation' => null,
            ],
            [
                'name' => 'Training',
                'description' => 'A User must have completed mandatory training before requesting data access.',
                'entity_model_type_id' => 1,
                'calls_file' => 1,
                'file_path' => 'user_training.json',
                'calls_operation' => 0,
                'operation' => null,
            ],
            [
                'name' => 'Data security compliance',
                'description' => 'An organisation must have Cyber Essentials and ISO27001 accreditations in effect.',
                'entity_model_type_id' => 1,
                'calls_file' => 1,
                'file_path' => 'user_training_expiry.json',
                'calls_operation' => 0,
                'operation' => null,
            ],
            [
                'name' => 'Delegate/Key Contact',
                'description' => 'An Organisation must have at least one Delegate/Key Contact to confirm a User\'s behaviour within secure data environments.',
                'entity_model_type_id' => 1,
                'calls_file' => 1,
                'file_path' => 'organisation_delegate.json',
                'calls_operation' => 0,
                'operation' => null,
            ],
        ],
        'validation_rules' => [
            [
                'name' => 'Mandatory Training',
                'description' => 'Has all Network mandatory training and awareness been completed?',
                'entity_model_type_id' => 2,
                'calls_file' => 0,
                'file_path' => null,
                'calls_operation' => 1,
                'operation' => '',
            ],
            [
                'name' => 'User self-declaration',
                'description' => 'Does the user\'s self-declaration match the organisation\'s account of the user?',
                'entity_model_type_id' => 2,
                'calls_file' => 0,
                'file_path' => null,
                'calls_operation' => 1,
                'operation' => '',
            ],
            [
                'name' => 'Allegations of Misconduct',
                'description' => 'Have any allegations of misconduct in research or other unacceptable research behaviour ever been upheld against the User?',
                'entity_model_type_id' => 2,
                'calls_file' => 0,
                'file_path' => null,
                'calls_operation' => 1,
                'operation' => '',
            ],
            [
                'name' => 'Criminal Activity',
                'description' => 'Has the User declared any criminal record activity deemed by the NSD retained Organisation?',
                'entity_model_type_id' => 2,
                'calls_file' => 0,
                'file_path' => null,
                'calls_operation' => 1,
                'operation' => '',
            ],
            [
                'name' => 'Organisation confirmation',
                'description' => 'Have the nominated organisations confirmed the User?',
                'entity_model_type_id' => 2,
                'calls_file' => 0,
                'file_path' => null,
                'calls_operation' => 1,
                'operation' => '',
            ],
        ],
    ],
];
