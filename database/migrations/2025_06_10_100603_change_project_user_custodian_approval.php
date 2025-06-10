<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        $originalTableName = 'project_user_has_custodian_approval';
        $newTableName = 'project_has_user_custodian_approval';

        Schema::create($newTableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('project_has_user_id');
            $table->unsignedBigInteger('custodian_id');

            $table->tinyInteger('approved')->default(0);
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('project_has_user_id', 'pucat_phuid_fk')
                ->references('id')
                ->on('project_has_users')
                ->onDelete('cascade');

            $table->foreign('custodian_id', 'pucat_custid_fk')
                ->references('id')
                ->on('custodians')
                ->onDelete('cascade')
                ->after('project_has_user_id');

            $table->foreign('custodian_id')
                ->references('id')
                ->on('custodians')
                ->onDelete('cascade');
        });

        DB::table($newTableName)->insertUsing(
            ['project_has_user_id', 'custodian_id', 'approved', 'comment', 'created_at'],
            DB::table($originalTableName . ' as puca')
                ->select([
                    'phu.id as project_has_user_id',
                    'puca.custodian_id',
                    'puca.approved',
                    'puca.comment',
                    'puca.created_at'
                ])
                ->join('projects as p', 'puca.project_id', '=', 'p.id')
                ->join('users as u', 'puca.user_id', '=', 'u.id')
                ->join('registries as r', 'u.registry_id', '=', 'r.id')
                ->join('project_has_users as phu', function ($join) {
                    $join->on('phu.project_id', '=', 'puca.project_id')
                        ->on('phu.user_digital_ident', '=', 'r.digi_ident');
                })
        );

        Schema::dropIfExists($originalTableName);
    }

    public function down(): void
    {
        $originalTableName = 'project_user_has_custodian_approval';
        $newTableName = 'project_has_user_custodian_approval';

        Schema::create($originalTableName, function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('custodian_id');
            $table->tinyInteger('approved')->default(0);
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('custodian_id')
                ->references('id')
                ->on('custodians')
                ->onDelete('cascade');
        });

        DB::table($originalTableName)->insertUsing(
            ['project_id', 'user_id', 'custodian_id', 'approved', 'comment', 'created_at'],
            DB::table($newTableName . ' as phuca')
                ->select([
                    'phu.project_id',
                    'u.id as user_id',
                    'phuca.custodian_id',
                    'phuca.approved',
                    'phuca.comment',
                    'phuca.created_at'
                ])
                ->join('project_has_users as phu', 'phuca.project_has_user_id', '=', 'phu.id')
                ->join('registries as r', 'phu.user_digital_ident', '=', 'r.digi_ident')
                ->join('users as u', 'r.id', '=', 'u.registry_id')
        );

        // Drop the new table
        Schema::dropIfExists($newTableName);
    }
};
