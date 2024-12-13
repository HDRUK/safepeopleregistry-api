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
        Schema::table('organisations', function (Blueprint $table) {
            $table->string('charity_registration_id')->nullable();
            $table->string('ror_id')->nullable();
            $table->string('website')->nullable();
            $table->boolean('smb_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('charity_registration_id')->nullable();
            $table->dropColumn('ror_id')->nullable();
            $table->dropColumn('website')->nullable();
            $table->dropColumn('smb_status')->nullable();
        });
    }
};
