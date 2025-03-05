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
        Schema::create('registry_has_trainings', function (Blueprint $table) {
            $table->bigInteger('registry_id');
            $table->bigInteger('training_id');

            $table->index('registry_id');
            $table->index('training_id');
        });

        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn('registry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registry_has_trainings');

        Schema::table('trainings', function (Blueprint $table) {
            $table->bigInteger('registry_id');
        });
    }
};
