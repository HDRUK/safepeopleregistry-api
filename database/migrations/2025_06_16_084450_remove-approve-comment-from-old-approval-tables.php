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
        Schema::table('custodian_has_project_has_user', function (Blueprint $table) {
            $table->dropColumn(['approved', 'comment']);
        });

        Schema::table('custodian_has_project_has_organisation', function (Blueprint $table) {
            $table->dropColumn(['approved', 'comment']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custodian_has_project_has_user', function (Blueprint $table) {
            $table->tinyInteger('approved')->default(0)->after('custodian_id');
            $table->text('comment')->nullable()->after('approved');
        });

        Schema::table('custodian_has_project_has_organisation', function (Blueprint $table) {
            $table->tinyInteger('approved')->default(0)->after('custodian_id');
            $table->text('comment')->nullable()->after('approved');
        });
    }
};
