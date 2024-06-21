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
        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('title', 255)->default('');
            $table->string('from', 7)->default('');
            $table->string('to', 7)->default('');
            $table->string('institute_name', 255)->default('');
            $table->mediumText('institute_address')->nullable()->default(null);
            $table->string('institute_identifier', 255)->nullable()->default(null);
            $table->string('source', 255)->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educations');
    }
};
