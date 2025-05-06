<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_user_has_custodian_approval', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->string('user_digital_ident', 255);
            $table->unsignedBigInteger('custodian_id');

            $table->unique(['project_id', 'user_digital_ident', 'custodian_id'], 'uq_project_user_custodian');

            $table->boolean('approved')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_user_has_custodian_approval');
    }
};
