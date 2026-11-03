<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            // When the current SRO took the role. Null for SROs appointed before this was recorded.
            $table->timestamp('sro_appointed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('sro_appointed_at');
        });
    }
};
