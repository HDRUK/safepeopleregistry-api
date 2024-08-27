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
            $table->string('ce_certification_num', 255)->nullable()->change();
            $table->text('applicant_names')->nullable()->change();
            $table->text('funders_and_sponsors')->nullable()->change();
            $table->text('sub_license_arrangements')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->string('ce_certification_num', 255)->default('')->change();
            $table->text('applicant_names')->change();
            $table->text('funders_and_sponsors')->change();
            $table->text('sub_license_arrangements')->change();
        });
    }
};
