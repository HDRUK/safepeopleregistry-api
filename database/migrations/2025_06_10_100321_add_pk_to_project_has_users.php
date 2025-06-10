<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_has_users', function (Blueprint $table) {
            $table->bigIncrements('id')->first();

            $table->unique(
                ['project_id', 'user_digital_ident', 'project_role_id', 'affiliation_id'],
                'phu_unique_key'
            );
        });
    }

    public function down(): void
    {
        Schema::table('project_has_users', function (Blueprint $table) {
            $table->dropUnique('phu_unique_key');
            $table->dropColumn('id');
        });
    }
};
