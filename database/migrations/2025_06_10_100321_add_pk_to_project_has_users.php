<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('project_has_users_temp', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->onDelete('cascade');

            $table->string('user_digital_ident', 255);

            $table->unsignedBigInteger('project_role_id');
            $table->foreign('project_role_id')
                ->references('id')
                ->on('project_roles')
                ->onDelete('cascade');

            $table->unsignedBigInteger('affiliation_id');
            $table->foreign('affiliation_id')
                ->references('id')
                ->on('affiliations')
                ->onDelete('cascade');

            $table->tinyInteger('primary_contact')->default(0);
        });

        DB::statement("
            INSERT INTO project_has_users_temp (
                project_id, 
                user_digital_ident, 
                project_role_id, 
                affiliation_id, 
                primary_contact
            )
            SELECT 
                project_id, 
                user_digital_ident, 
                project_role_id, 
                affiliation_id, 
                primary_contact
            FROM project_has_users
        ");

        Schema::drop('project_has_users');

        Schema::rename('project_has_users_temp', 'project_has_users');
    }

    public function down(): void
    {
        Schema::table('project_has_users', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }
};
