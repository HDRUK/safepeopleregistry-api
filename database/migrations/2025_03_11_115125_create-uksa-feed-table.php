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
        Schema::create('uksa_live_feeds', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('organisation_name', 255);
            $table->string('accreditation_number', 32);
            $table->string('accreditation_type', 32);
            $table->string('expiry_date', 12);
            $table->string('public_record', 3);
            $table->string('stage', 32);

            $table->index('accreditation_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uksa_live_feed');
    }
};
