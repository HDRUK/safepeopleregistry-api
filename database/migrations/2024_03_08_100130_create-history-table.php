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
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('employment_id');
            $table->bigInteger('endorsement_id');
            $table->bigInteger('infringement_id');
            $table->bigInteger('project_id');
            $table->bigInteger('access_key_id');
            $table->string('issuer_identifier', 255);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
