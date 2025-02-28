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
        Schema::table('identities', function (Blueprint $table) {
            $table->tinyInteger('idvt_result')->nullable()->default(null);
            $table->double('idvt_result_perc')->nullable()->default(null);
            $table->json('idvt_errors')->nullable()->default(null);
            $table->dateTime('idvt_completed_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identities', function (Blueprint $table) {
            $table->dropColumn('idvt_result');
            $table->dropColumn('idvt_result_perc');
            $table->dropColumn('idvt_errors');
            $table->dropColumn('idvt_completed_at');
        });
    }
};
