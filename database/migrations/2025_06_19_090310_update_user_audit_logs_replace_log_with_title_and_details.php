<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('user_audit_logs', function (Blueprint $table) {
            $table->renameColumn('class', 'entity_type');
        });

        Schema::table('user_audit_logs', function (Blueprint $table) {
            $table->json('details');
            $table->unsignedBigInteger('entity_id')->after('entity_type');
            $table->unsignedBigInteger('auditor_user_id')->after('user_id')->nullable();
        });

        DB::table('user_audit_logs')->update([
            'details' => DB::raw('log')
        ]);

        Schema::table('user_audit_logs', function (Blueprint $table) {
            $table->dropColumn('log');
        });
    }

    public function down(): void
    {
        Schema::table('user_audit_logs', function (Blueprint $table) {
            $table->text('log')->nullable();
            $table->renameColumn('entity_type', 'class');
        });

        DB::table('user_audit_logs')->update([
            'log' => DB::raw('details')
        ]);

        Schema::table('user_audit_logs', function (Blueprint $table) {
            $table->dropColumn(['details', 'auditor_user_id', 'entity_id']);
        });
    }
};
