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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('unique_id', 255)->nullable()->default(null)->change();
            $table->text('lay_summary')->nullable()->default(null)->change();
            $table->text('public_benefit')->nullable()->default(null)->change();
            $table->string('request_category_type', 255)->nullable()->default(null)->change();
            $table->text('technical_summary')->nullable()->default(null)->change();
            $table->text('other_approval_committees')->nullable()->default(null)->change();
            $table->dateTime('start_date')->nullable()->default(null)->change();
            $table->dateTime('end_date')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('unique_id', 255)->nullable(false)->change();
            $table->text('lay_summary')->nullable(false)->change();
            $table->text('public_benefit')->nullable(false)->change();
            $table->string('request_category_type', 255)->nullable(false)->change();
            $table->text('technical_summary')->nullable(false)->change();
            $table->text('other_approval_committees')->nullable(false)->change();
            $table->dateTime('start_date')->nullable(false)->change();
            $table->dateTime('end_date')->nullable(false)->change();
        });
    }
};
