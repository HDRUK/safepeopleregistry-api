<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $originalTableName = 'project_user_has_custodian_approval';
        $newTableName = 'project_has_user_custodian_approval';

        DB::statement("CREATE TABLE {$newTableName} LIKE {$originalTableName}");
        DB::statement("INSERT INTO {$newTableName} SELECT * FROM {$originalTableName}");


        Schema::table($newTableName, function (Blueprint $table) {
            $table->unsignedBigInteger('project_has_user_id')->after('custodian_id');
        });


        DB::table($newTableName . ' as phuca')
            ->join('users as u', 'phuca.user_id', '=', 'u.id')
            ->join('registries as r', 'u.registry_id', '=', 'r.id')
            ->join('project_has_users as phu', function ($join) {
                $join->on('phu.project_id', '=', 'phuca.project_id')
                    ->on('phu.user_digital_ident', '=', 'r.digi_ident');
            })
            ->update([
                'phuca.project_has_user_id' => DB::raw('phu.id')
            ]);

        Schema::table($newTableName, function (Blueprint $table) {
            $table->dropColumn(['project_id']);
            $table->dropColumn(['user_id']);
        });

        Schema::table($newTableName, function (Blueprint $table) {
            $table->foreign('project_has_user_id', 'pucat_phuid_fk')
                ->references('id')
                ->on('project_has_users')
                ->onDelete('cascade');

            $table->foreign('custodian_id', 'pucat_custid_fk')
                ->references('id')
                ->on('custodians')
                ->onDelete('cascade')
                ->after('project_has_user_id');
        });

        Schema::dropIfExists($originalTableName);
    }

    public function down(): void
    {

        $originalTableName = 'project_user_has_custodian_approval';
        $newTableName      = 'project_has_user_custodian_approval';

        DB::statement("CREATE TABLE {$originalTableName} LIKE {$newTableName}");

        Schema::table($originalTableName, function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->after('custodian_id');
            $table->unsignedBigInteger('user_id')->after('project_id');
        });

        DB::table("{$originalTableName} as puha")
            ->join('project_has_users as phu', 'puha.project_has_user_id', '=', 'phu.id')
            ->join('registries as r',        'phu.user_digital_ident', '=', 'r.digi_ident')
            ->join('users as u',             'u.registry_id',         '=', 'r.id')
            ->update([
                'puha.project_id' => DB::raw('phu.project_id'),
                'puha.user_id'    => DB::raw('u.id'),
            ]);

        Schema::table($originalTableName, function (Blueprint $table) {
            $table->dropColumn(['project_has_user_id']);
        });

        Schema::dropIfExists($newTableName);
    }
};
