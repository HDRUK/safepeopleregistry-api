<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::table('features')->updateOrInsert(
            ['name' => 'CustodianReadOnlyEnabled', 'scope' => '__laravel_null'],
            [
                'value' => 'true',
                'description' => 'Enable read-only access for custodian users. When off, read-only users are shown a notice to contact support.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('features')->where('name', 'CustodianReadOnlyEnabled')->delete();
    }
};
