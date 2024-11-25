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
            $table->integer('sector_id');
            $table->tinyInteger('dsptk_certified')->after('dsptk_ods_code')->default(0);
            $table->string('iso_27001_certification_num', 255)->nullable();

            $table->index('sector_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('dsptk_certified');
            $table->dropColumn('sector_id');
            $table->dropColumn('iso_27001_certification_num');
        });
    }
};
