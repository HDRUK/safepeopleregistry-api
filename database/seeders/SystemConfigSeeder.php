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
                'value' => '5M',
            ],
            [
                'name' => 'SUPPORTED_FILETYPES',
                'value' => '.pdf,.doc,.docx,.png,.jpeg,.csv',
            ]
        ];

        foreach ($configs as $c) {
            SystemConfig::create([
                'name' => $c['name'],
                'value' => $c['value'],
            ]);
        }
    }
}
