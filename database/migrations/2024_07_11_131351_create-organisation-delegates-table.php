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
        Schema::create('organisation_delegates', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->tinyInteger('is_dpo');
            $table->tinyInteger('is_hr');
            $table->string('email', 255);
            $table->tinyInteger('priority_order');
            $table->bigInteger('organisation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_delegates');
    }
};
