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
        Schema::table('pending_invites', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable(true)->after('organisation_id');
            $table->string('type')->nullable(true)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pending_invites', function (Blueprint $table) {
            $table->dropColumn(['project_id', 'type']);
        });
    }
};
