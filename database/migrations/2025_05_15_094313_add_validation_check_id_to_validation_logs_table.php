<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('validation_logs', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('validation_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('validation_check_id')->nullable()->after('entity_type');
            $table->foreign('validation_check_id')
                ->references('id')
                ->on('validation_checks')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('validation_logs', function (Blueprint $table) {
            $table->dropForeign(['validation_check_id']);
            $table->string('name')->nullable();
        });

        Schema::table('validation_logs', function (Blueprint $table) {
            $table->dropColumn('validation_check_id');
        });
    }
};
