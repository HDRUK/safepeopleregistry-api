<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::table('features')->updateOrInsert(
            ['name' => 'HubspotEnabled', 'scope' => '__laravel_null'],
            [
                'value' => 'false',
                'description' => 'Enable Hubspot integration. This overrides the HUBSPOT_INTEGRATION_ENABLED environment variable.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('features')->where('name', 'HubspotEnabled')->delete();
    }
};
