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
        Schema::create('professional_registrations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('member_id', 255);
            $table->string('name', 255);
        });

        Schema::create('registry_has_professional_registrations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('professional_registration_id');
            $table->bigInteger('registry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professional_registrations');
        Schema::dropIfExists('registry_has_professional_registrations');
    }
};
