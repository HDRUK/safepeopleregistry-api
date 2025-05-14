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
        Schema::create('custodian_has_validation_check', function (Blueprint $table) {
            $table->foreignId('custodian_id')->constrained()->cascadeOnDelete();
            $table->foreignId('validation_check_id')->constrained()->cascadeOnDelete();
            $table->primary(['custodian_id', 'validation_check_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custodian_has_validation_check');
    }
};
