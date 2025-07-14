<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrganisationHasSubsidiariesCascadeDelete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organisation_has_subsidiaries', function (Blueprint $table) {
            $table->unsignedBigInteger('subsidiary_id')->change();

            $table->foreign('subsidiary_id')->references('id')->on('subsidiaries')->onDelete('cascade')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organisation_has_subsidiaries', function (Blueprint $table) {
            $table->dropForeign(['subsidiary_id']);
            $table->bigInteger('subsidiary_id')->change();
        });
    }
}
