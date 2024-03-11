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
        Schema::create('infringements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('reported_by'); // issuer id mapping
            $table->text('comment')->nullable();
            $table->bigInteger('raised_on'); // project id mapping
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infringements');
    }
};
