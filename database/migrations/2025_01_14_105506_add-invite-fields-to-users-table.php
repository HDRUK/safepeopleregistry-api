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
        Schema::table('pending_invites', function (Blueprint $table) {
            $table->timestamp('invite_accepted_at')->nullable();
            $table->timestamp('invite_sent_at')->nullable();
            $table->timestamp('invite_code')->nullable();

            $table->bigInteger('organisation_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pending_invites', function (Blueprint $table) {
            $table->dropIfExists('invite_accepted_at');
            $table->dropIfExists('invite_sent_at');
            $table->dropIfExists('invite_code');

            $table->bigInteger('organisation_id')->nullable(false)->change();
        });
    }
};
