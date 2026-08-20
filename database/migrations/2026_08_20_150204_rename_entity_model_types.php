<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('entity_model_types', 'decision_model_types');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('decision_model_types', 'entity_model_types');
    }
};
