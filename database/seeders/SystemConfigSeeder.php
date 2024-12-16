<?php

namespace Database\Seeders;

use App\Models\SystemConfig;
use Illuminate\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemConfig::truncate();

        $configs = [
            [
                'name' => 'PER_PAGE',
                'value' => 25,
                'description' => 'Default total items to display for pagination',
            ],
            [
                'name' => 'MAX_FILESIZE',
                'value' => '10', // Mb
                'description' => 'Default maximum file size for uploads',
            ],
            [
                'name' => 'SUPPORTED_FILETYPES',
                'value' => 'pdf,doc,docx,png,jpeg,jpg,tsv,csv',
                'description' => 'Pre-defined list of accepted file types accepted for uploads',
            ],
            [
                'name' => 'FILE_UPLOAD_TYPES',
                'value' => 'USER_EVIDENCE,ORG_EVIDENCE,RESEARCHER_LIST',
                'description' => 'Pre-defined list of accepted uploadable file types to dictate the content',
            ],
            [
                'name' => 'VALIDATION_SCHEMA',
                'value' => '
                    {
                        "validationSchema": {
                            "password": {
                                "type": "string",
                                "minLength": 8,
                                "maxLength": 32,
                                "pattern": "^(?=.*\\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,32}$"
                            },
                            "email": {
                                "type": "string",
                                "pattern": "^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\\.[a-zA-Z]{2,6}$"
                            },
                            "ce_certification_num": {
                                "type": "string",
                                "pattern": "^([a-zA-Z0-9]+-){4}[a-zA-Z0-9]+$"
                            },
                            "orc_id": {
                                "type": "string",
                                "pattern": "^[\\\d]{4}-[\\\d]{4}-[\\\d]{4}-[\\\d]{3}(0|9|X)$"
                            },
                            "company_number": {
                                "type": "string",
                                "pattern": "^((AC|ZC|FC|GE|LP|OC|SE|SA|SZ|SF|GS|SL|SO|SC|ES|NA|NZ|NF|GN|NL|NC|R0|NI|EN|\\\d{2}|SG|FE)\\\d{5}(\\\d|C|R))|((RS|SO)\\\d{3}(\\\d{3}|\\\d{2}[WSRCZF]|\\\d(FI|RS|SA|IP|US|EN|AS)|CUS))|((NI|SL)\\\d{5}[\\\dA])|(OC(([\\\dP]{5}[CWERTB])|([\\\dP]{4}(OC|CU))))$"
                            },
                            "postcode": {
                                "type": "string",
                                "maxLength": 8,
                                "pattern": "[A-Za-z]{1,2}[0-9Rr][0-9A-Za-z]? [0-9][ABD-HJLNP-UW-Zabd-hjlnp-uw-z]{2}"
                            },
                            "otp": {
                                "type": "string",
                                "minLength": 6,
                                "maxLength": 6
                            }
                        }
                    }
                ',
                'description' => 'Default validation for frontend form elements',
            ],
            [
                'name' => 'IDVT_ORG_VERIFY_PERCENT',
                'value' => 88,
                'description' => 'Default percentage required for passing Organisation verification',
            ],
            [
                'name' => 'IDVT_ORG_SIC_WEIGHT_DECREASE',
                'value' => 0.05,
                'description' => 'Default weighting decrement value for Organisations within undesirable SIC codes',
            ],
        ];

        foreach ($configs as $c) {
            SystemConfig::create([
                'name' => $c['name'],
                'value' => $c['value'],
                'description' => $c['description'],
            ]);
        }
    }
}
