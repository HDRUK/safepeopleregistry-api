<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// note: may need this in the future if we do custodian approvals linked to this
return new class () extends Migration {
    public function up(): void
    {
        Schema::table('project_has_users', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });
    }

    public function down(): void
    {
        Schema::table('project_has_users', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }
};
