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
            $table->unsignedBigInteger('ce_expiry_evidence')->after('ce_expiry_date')->nullable();
            $table->unsignedBigInteger('ce_plus_expiry_evidence')->after('ce_plus_expiry_date')->nullable();
            $table->unsignedBigInteger('iso_expiry_evidence')->after('iso_expiry_date')->nullable();
            $table->unsignedBigInteger('dsptk_expiry_evidence')->after('dsptk_expiry_date')->nullable();

            $table->foreign('ce_expiry_evidence')->references('id')->on('files')->onDelete('set null');
            $table->foreign('ce_plus_expiry_evidence')->references('id')->on('files')->onDelete('set null');
            $table->foreign('iso_expiry_evidence')->references('id')->on('files')->onDelete('set null');
            $table->foreign('dsptk_expiry_evidence')->references('id')->on('files')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropForeign(['ce_expiry_evidence']);
            $table->dropForeign(['ce_plus_expiry_evidence']);
            $table->dropForeign(['iso_expiry_evidence']);
            $table->dropForeign(['dsptk_expiry_evidence']);

            $table->dropColumn('ce_expiry_evidence');
            $table->dropColumn('ce_plus_expiry_evidence');
            $table->dropColumn('iso_expiry_evidence');
            $table->dropColumn('dsptk_expiry_evidence');
        });
    }
};
