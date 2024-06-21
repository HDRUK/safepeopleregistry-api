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
        Schema::create('accreditations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('awarded_at', 7)->default(''); // in form of MM/YYYY
            $table->string('awarding_body_name', 255)->default('');
            $table->string('awarding_body_ror', 255)->nullable()->default(null);
            $table->text('title')->default('');
            $table->string('expires_at', 7)->default(''); // in form of MM/YYYY
            $table->string('awarded_locale', 255)->default(''); // country
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accreditations');
    }
};
