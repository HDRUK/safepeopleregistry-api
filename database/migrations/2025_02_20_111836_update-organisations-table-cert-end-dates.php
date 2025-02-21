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
            $table->date('ce_expiry_date')->after('ce_certification_num')->nullable();
            $table->date('ce_plus_expiry_date')->after('ce_plus_certification_num')->nullable();
            $table->date('iso_expiry_date')->after('iso_27001_certification_num')->nullable();
            $table->date('dsptk_expiry_date')->after('dsptk_certified')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('ce_expiry_date');
            $table->dropColumn('ce_plus_expiry_date');
            $table->dropColumn('iso_expiry_date');
            $table->dropColumn('dsptk_expiry_date');
        });
    }
};
