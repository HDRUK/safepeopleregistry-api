<?php

return [

    /*
        Default configuration for SPEEDI-AS API Deployment
    */
    'issuers' => [
        [
            'name' => 'SAIL Databank',
            'contact_email' => 'sail@email.com',
        ],
        [
            'name' => 'NHS England',
            'contact_email' => 'nhse@email.com',
        ],
    ],
    'invite_status' => [
        'PENDING' => 'PENDING',
        'COMPLETE' => 'COMPLETE',
    ],
];
