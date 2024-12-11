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
            $table->string('charity_registration_id');
            $table->string('ror_id');
            $table->string('website');
            $table->boolean('smb_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
          $table->dropColumn('charity_registration_id');
          $table->dropColumn('ror_id');
          $table->dropColumn('website');
          $table->dropColumn('smb_status');
        });
    }
};
