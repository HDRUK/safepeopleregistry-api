<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accreditations', function (Blueprint $table) {
            // Someone couldn't count...
            $table->string('awarded_at', 10)->change();
            $table->string('expires_at', 10)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accreditations', function (Blueprint $table) {
            $table->string('awarded_at', 7)->change();
            $table->string('expires_at', 7)->change();
        });
    }
};
