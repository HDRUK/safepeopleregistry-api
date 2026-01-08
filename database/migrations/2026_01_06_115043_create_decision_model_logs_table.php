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
        Schema::create('decision_model_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('decision_model_id');
            $table->unsignedBigInteger('custodian_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('model_type')->nullable();
            $table->tinyInteger('status')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decision_model_logs');
    }
};
