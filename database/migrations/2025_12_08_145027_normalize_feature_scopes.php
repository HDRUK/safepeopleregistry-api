<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('features')) {
            return;
        }

        DB::table('features')
            ->whereNull('scope')
            ->update(['scope' => '__laravel_null']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('features')
            ->where('scope', '__laravel_null')
            ->update(['scope' => null]);
    }
};

