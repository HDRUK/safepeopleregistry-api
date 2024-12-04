<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectRoleIdToProjectHasUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_has_users', function (Blueprint $table) {
            $table->unsignedBigInteger('project_role_id')->nullable()->after('user_digital_ident');
            $table->foreign('project_role_id')->references('id')->on('project_roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_has_users', function (Blueprint $table) {
            $table->dropForeign(['project_role_id']);
            $table->dropColumn('project_role_id');
        });
    }
}
