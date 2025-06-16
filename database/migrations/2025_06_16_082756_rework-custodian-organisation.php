<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        $originalTableName = 'organisation_has_custodian_approvals';
        $newTableName = 'custodian_has_project_has_organisation';

        if (!Schema::hasTable($newTableName)) {
            Schema::create($newTableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('project_has_organisation_id');
                $table->unsignedBigInteger('custodian_id');

                $table->tinyInteger('approved')->default(0);
                $table->text('comment')->nullable();
                $table->timestamps();

                $table->foreign('project_has_organisation_id', 'cho_phoid_fk')
                    ->references('id')
                    ->on('project_has_organisations')
                    ->onDelete('cascade');

                $table->foreign('custodian_id', 'cho_custid_fk')
                    ->references('id')
                    ->on('custodians')
                    ->onDelete('cascade');
            });
        }

        Schema::dropIfExists($originalTableName);
    }

    public function down(): void
    {
        $originalTableName = 'organisation_has_custodian_approvals';
        $newTableName = 'custodian_has_organisation';

        Schema::create($originalTableName, function (Blueprint $table) {
            $table->unsignedBigInteger('organisation_id');
            $table->unsignedBigInteger('custodian_id');
            $table->tinyInteger('approved')->default(0);
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->primary(['organisation_id', 'custodian_id']);

            $table->foreign('organisation_id')
                ->references('id')
                ->on('organisations')
                ->onDelete('cascade');

            $table->foreign('custodian_id')
                ->references('id')
                ->on('custodians')
                ->onDelete('cascade');
        });

        // Drop the new table
        Schema::dropIfExists($newTableName);
    }
};
