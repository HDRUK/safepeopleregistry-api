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
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('organisation_name', 255);
            $table->string('address_1', 255);
            $table->string('address_2', 255)->nullable();
            $table->string('town', 255);
            $table->string('county', 255);
            $table->string('country', 255);
            $table->string('postcode', 8);
            $table->string('lead_applicant_organisation_name', 255)->nullable();
            $table->string('lead_applicant_email', 255)->nullable()->default(null);
            $table->string('password')->nullable()->default(null);
            $table->string('organisation_unique_id', 255);
            $table->text('applicant_names');
            $table->text('funders_and_sponsors');
            $table->text('sub_license_arrangements');
            $table->tinyInteger('verified')->default(false);
            $table->string('dsptk_ods_code')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisations');
    }
};
