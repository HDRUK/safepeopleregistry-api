<?php

namespace Database\Seeders;

use App\Models\SystemConfig;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            ],
            [
                'name' => 'MAX_FILESIZE',
                'value' => '10', // Mb
            ],
            [
                'name' => 'SUPPORTED_FILETYPES',
                'value' => 'pdf,doc,docx,png,jpeg,jpg,tsv,csv',
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
            ],
        ];

        foreach ($configs as $c) {
            SystemConfig::create([
                'name' => $c['name'],
                'value' => $c['value'],
            ]);
        }
    }
}
