<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::table('features')->updateOrInsert(
            ['name' => 'SroRequirementEnabled', 'scope' => '__laravel_null'],
            [
                'value' => 'false',
                'description' => 'Require an SRO on an Organisation account.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('features')->where('name', 'SroRequirementEnabled')->delete();
    }
};
