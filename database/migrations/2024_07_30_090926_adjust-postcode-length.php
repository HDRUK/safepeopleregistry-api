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
        Schema::table('identities', function (Blueprint $table) {
            $table->string('postcode', 24)->change();
        });

        Schema::table('organisations', function (Blueprint $table) {
            $table->string('postcode', 24)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identities', function (Blueprint $table) {
            $table->string('postcode', 8)->change();
        });

        Schema::table('organisations', function (Blueprint $table) {
            $table->string('postcode', 8)->change();
        });
    }
};
