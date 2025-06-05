<?php

return [

    /*
        Default configuration for SOURSD API Deployment
    */
    'custodians' => [
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
    'system' => [
        // Application configuration
        'app_name' => env('APP_NAME', 'Safe People Registry'),
        'app_env' => env('APP_ENV', 'production'),
        'app_url' => env('APP_URL', 'https://safepeopleregistry.org'),
        'support_email' => env('SUPPORT_EMAIL', 'support@safepeopleregistry.org'),
        'invite_time_hours' => env('INVITE_TIME_HOURS', 24),
        'registry_salt_1' => env('REGISTRY_SALT_1'),
        'registry_salt_2' => env('REGISTRY_SALT_2'),
        // System configuration
        'idvt_supplier_secret_key' => env('IDVT_SUPPLIER_SECRET_KEY'),
        // Keycloak configuration
        'keycloak_realm' => env('KEYCLOAK_REALM', 'SOURSD'),
        'keycloak_base_url' => env('KEYCLOAK_BASE_URL'),
        'keycloak_client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
        'keycloak_client_id' => env('KEYCLOAK_CLIENT_ID'),
        // Service configuration
        'clam_av_service_url' => env('CLAM_AV_SERVICE_URL', 'http://clamav:3001/scan_file'),
        'idvt_org_scanner' => env('IDVT_ORG_SCANNER'),
        'idvt_comapnies_house_url' => env('IDVT_COMPANIES_HOUSE_URL'),
        'ons_acredited_researcher_list_url' => env('ONS_ACREDITED_RESEARCHER_LIST_URL'),
        'ons_acredited_researcher_list_page_url' => env('ONS_ACCREDITED_RESEARCHER_LIST_PAGE_URL'),
        'ons_column_start_index' => env('ONS_COLUMN_START_INDEX', 1),
        'ons_row_start_index' => env('ONS_ROW_START_INDEX', 6),
        'orcid_url' => env('ORCID_URL'),
        'ordcid_app_id' => env('ORCID_APP_ID'),
        'orcid_redirect_url' => env('ORCID_REDIRECT_URL'),
        'orcid_auth_url' => env('ORCID_AUTH_URL'),
        'orcid_client_id' => env('ORCID_CLIENT_ID'),
        'orcid_client_secret' => env('ORCID_CLIENT_SECRET'),
    ],
];
