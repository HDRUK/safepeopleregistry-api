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

        Schema::create('validation_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('entity');
            $table->nullableMorphs('secondary_entity');
            $table->nullableMorphs('tertiary_entity');
            $table->string('name');
            $table->timestamp('completed_at')->nullable();
            $table->tinyInteger('manually_confirmed')->default(0);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validation_logs');
    }


};
