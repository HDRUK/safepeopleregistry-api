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
        Schema::create('organisation_has_subsidiaries', function (Blueprint $table) {
            $table->bigInteger('organisation_id');
            $table->bigInteger('subsidiary_id');

            $table->index('organisation_id');
            $table->index('subsidiary_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_has_subsidiaries');
    }
};
