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
            $table->string('ods_id')->nullable();

            $table->date('dsptk_date_last_published')->nullable();

            $table->string('ico_registration_id')->nullable();
            $table->date('ico_date_registered')->nullable();
            $table->date('ico_expiry_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('ods_id')->nullable();

            $table->dropColumn('dsptk_date_last_published')->nullable();

            $table->dropColumn('ico_registration_id')->nullable();
            $table->dropColumn('ico_date_registered')->nullable();
            $table->dropColumn('ico_expiry_date')->nullable();
        });
    }
};
