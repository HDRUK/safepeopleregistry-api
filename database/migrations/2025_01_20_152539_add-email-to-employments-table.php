<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employments', function (Blueprint $table) {
            $table->string('email', 255);
            $table->tinyInteger('email_verified')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employments', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->dropColumn('email_verified');
        });
    }
};
