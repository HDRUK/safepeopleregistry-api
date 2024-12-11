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
        Schema::table('custodians', function (Blueprint $table) {
            $table->dateTime('invite_sent_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custodians', function (Blueprint $table) {
            $table->dropIfExists('invite_sent_at');
        });
    }
};
