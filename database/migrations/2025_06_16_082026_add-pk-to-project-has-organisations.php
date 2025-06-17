<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('project_has_organisations_temp', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->onDelete('cascade');

            $table->unsignedBigInteger('organisation_id')->nullable();
            $table->foreign('organisation_id')
                ->references('id')
                ->on('organisations')
                ->onDelete('cascade');
        });

        DB::statement("
            INSERT INTO project_has_organisations_temp (
                project_id, 
                organisation_id
            )
            SELECT 
                project_id, 
                organisation_id 
            FROM project_has_organisations
        ");

        Schema::drop('project_has_organisations');

        Schema::rename('project_has_organisations_temp', 'project_has_organisations');
    }

    public function down(): void
    {
        Schema::table('project_has_organisations', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }
};
