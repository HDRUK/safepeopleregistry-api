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
        Schema::table('project_details', function (Blueprint $table) {
            $table->tinyInteger('access_type')->nullable();
            $table->mediumText('data_privacy')->nullable();
            $table->mediumText('research_outputs')->nullable();
            $table->mediumText('data_assets')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_details', function (Blueprint $table) {
            $table->dropColumn('access_type');
            $table->dropColumn('data_privacy');
            $table->dropColumn('research_outputs');
            $table->dropColumn('data_assets');
        });
    }
};
