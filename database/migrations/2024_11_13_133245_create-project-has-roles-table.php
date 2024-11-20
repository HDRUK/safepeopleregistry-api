<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_has_project_roles', function (Blueprint $table) {
            $table->bigInteger('project_id');
            $table->bigInteger('project_role_id');

            $table->index('project_id');
            $table->index('project_role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_has_project_roles');
    }
};
