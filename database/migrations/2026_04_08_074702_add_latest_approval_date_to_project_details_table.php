<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('project_details', function (Blueprint $table) {
            $table->timestamp('latest_approval_date')->nullable()->after('data_assets');
        });
    }

    public function down(): void
    {
        Schema::table('project_details', function (Blueprint $table) {
            $table->dropColumn('latest_approval_date');
        });
    }
};
