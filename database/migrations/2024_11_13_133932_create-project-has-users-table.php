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
        Schema::create('project_has_users', function (Blueprint $table) {
            $table->bigInteger('project_id');
            $table->string('user_digital_ident', 255);

            $table->index('project_id');
            $table->index('user_digital_ident');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_has_users');
    }
};
