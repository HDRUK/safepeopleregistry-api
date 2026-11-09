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
            $table->boolean('is_restricted')->default(false)->after('system_approved_at');
            $table->foreignId('created_by_custodian_id')
                ->nullable()
                ->after('is_restricted')
                ->constrained('custodians')
                ->nullOnDelete();
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->after('created_by_custodian_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropConstrainedForeignId('created_by_custodian_id');
            $table->dropColumn('is_restricted');
        });
    }
};
