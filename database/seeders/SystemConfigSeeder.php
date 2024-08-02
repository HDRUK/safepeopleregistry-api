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
                'name' => 'VALIDATION_SCHEMA',
                'value' => '
                    {
                        "validationSchema": {
                            "password": {
                                "type": "string",
                                "minLength": 8,
                                "maxLength": 32,
                                "pattern": "^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,32}$"
                            },
                            "email": {
                                "type": "string",
                                "pattern": "^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$"
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
