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
            $table->string('model_type');
            $table->unsignedBigInteger('subject_id');
            $table->tinyInteger('status')->default(false);
            $table->timestamps();
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
