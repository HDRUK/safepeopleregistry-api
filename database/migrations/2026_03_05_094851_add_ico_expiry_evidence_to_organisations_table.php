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
            $table->string('dsptk_status')->after('dsptk_certified')->nullable();
            $table->unsignedBigInteger('ico_expiry_evidence')->after('ico_expiry_date')->nullable();
            $table->foreign('ico_expiry_evidence')->references('id')->on('files')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropForeign(['ico_expiry_evidence']);
            $table->dropColumn('ico_expiry_evidence');
            $table->dropColumn('dsptk_status');
        });
    }
};
