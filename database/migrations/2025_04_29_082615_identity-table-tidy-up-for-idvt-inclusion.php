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
        // I swear SQLite gets worse with every version bump...
        //
        Schema::table('identities', function (Blueprint $table) {
            $table->dropColumn('selfie_path');
        });

        Schema::table('identities', function (Blueprint $table) {
            $table->dropColumn('passport_path');
        });

        Schema::table('identities', function (Blueprint $table) {
            $table->dropColumn('drivers_license_path');
        });

        Schema::table('identities', function (Blueprint $table) {
            $table->dropColumn('idvt_result_perc');
        });

        Schema::table('identities', function (Blueprint $table) {
            $table->dropColumn('idvt_errors');
        });

        Schema::table('identities', function (Blueprint $table) {
            $table->dropColumn('idvt_result');
        });

        Schema::table('identities', function (Blueprint $table) {
            $table->string('idvt_result_text')->nullable()->after('idvt_completed_at');
            $table->json('idvt_context')->nullable()->after('idvt_result_text');
            $table->tinyInteger('idvt_success')->default(0)->after('idvt_context');
            $table->string('idvt_identification_number')->nullable()->after('idvt_success');
            $table->string('idvt_document_type')->nullable()->after('idvt_identification_number');
            $table->string('idvt_document_number')->nullable()->after('idvt_document_type');
            $table->string('idvt_document_country')->nullable()->after('idvt_document_number');
            $table->string('idvt_document_valid_until')->nullable()->after('idvt_document_country');
            $table->string('idvt_attempt_id')->nullable()->after('idvt_document_valid_until');
            $table->string('idvt_context_id')->nullable()->after('idvt_attempt_id');
            $table->string('idvt_document_dob')->nullable()->after('idvt_context_id');

            $table->index('idvt_identification_number', 'idx_idvt_identification_number');
            $table->index('idvt_attempt_id', 'idx_idvt_attempt_id');
            $table->index('idvt_context_id', 'idx_idvt_context_id');
            $table->index('idvt_document_type', 'idx_idvt_document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not going backwards here
    }
};
