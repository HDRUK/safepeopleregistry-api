<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('project_has_users', function (Blueprint $table) {
            $table->index('user_digital_ident');
            $table->index(
                ['project_id', 'user_digital_ident', 'affiliation_id'],
                'phu_project_user_affiliation_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('project_has_users', function (Blueprint $table) {
            $table->dropIndex('project_has_users_user_digital_ident_index');
            $table->dropIndex('phu_project_user_affiliation_index');
        });
    }
};
