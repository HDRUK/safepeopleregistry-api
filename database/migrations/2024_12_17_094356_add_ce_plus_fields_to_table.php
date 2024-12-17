<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCePlusFieldsToTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('your_table_name', function (Blueprint $table) {
            $table->tinyInteger('ce_plus_certified')->default(0);
            $table->string('ce_plus_certification_num', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('your_table_name', function (Blueprint $table) {
            $table->dropColumn('ce_plus_certified');
            $table->dropColumn('ce_plus_certification_num');
        });
    }
}
