<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('project_user_has_custodian_approval', function (Blueprint $table) {
            $table->unsignedBigInteger('project_user_id');
            $table->unsignedBigInteger('custodian_id');

            $table->unique(['project_user_id', 'custodian_id'], 'uq_project_user_custodian');

            $table->foreign('project_user_id', 'fk_puca_project_user')
                ->references('id')
                ->on('project_has_users')
                ->onDelete('cascade');

            $table->foreign('custodian_id', 'fk_puca_custodian')
                ->references('id')
                ->on('custodians')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_user_has_custodian_approval');
    }
};
