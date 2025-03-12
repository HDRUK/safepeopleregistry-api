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
        Schema::create('model_states', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('state_id')->constrained();
            $table->morphs('stateable'); // Poly relation
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_states');
    }
};
