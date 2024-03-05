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
        Schema::create('affiliations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name', 255);
            $table->string('address_1', 255);
            $table->string('address_2', 255)->nullable();
            $table->string('town', 255);
            $table->string('county', 255);
            $table->string('country', 255);
            $table->string('postcode', 8);
            $table->string('delegate', 255)->nullable();
            $table->tinyInteger('verified')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliations');
    }
};
