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
        Schema::table('custodians', function (Blueprint $table) {
            $table->string('gateway_app_id', 255)->nullable();
            $table->string('gateway_client_id', 255)->nullable();

            $table->index('gateway_app_id');
            $table->index('gateway_client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custodians', function (Blueprint $table) {
            $table->dropColumn('gateway_app_id');
            $table->dropColumn('gateway_client_id');
        });
    }
};
