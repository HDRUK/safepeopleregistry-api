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
        Schema::table('organisations', function (Blueprint $table) {
            $table->tinyInteger('iso_27001_certified')->default(0);
            $table->tinyInteger('ce_certified')->default(0);
            $table->string('ce_certification_num', 255)->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('iso_27001_certified');
            $table->dropColumn('ce_certified');
            $table->dropColumn('ce_certification_num');
        });
    }
};
