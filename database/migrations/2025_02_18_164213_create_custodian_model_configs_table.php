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
        Schema::create('entity_model_types', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name', 255);
        });

        Schema::create('custodian_model_configs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('entity_model_id');
            $table->tinyInteger('active')->default(1);
            $table->bigInteger('custodian_id');

            $table->index('entity_model_id');
            $table->index('custodian_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_model_types');
        Schema::dropIfExists('custodian_model_configs');
    }
};
