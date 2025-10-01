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
        Schema::table('affiliations', function (Blueprint $table) {
            $table->boolean('current_employer')->default(false);
            $table->string('verification_code', 64)->nullable();
            $table->timestamp('verification_sent_at')->nullable();
            $table->timestamp('verification_confirmed_at')->nullable();
            $table->boolean('is_verified')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliations', function (Blueprint $table) {
            $table->dropColumn([
                'current_employer',
                'verification_code',
                'verification_sent_at',
                'verification_confirmed_at',
                'is_verified',
            ]);
        });
    }
};
