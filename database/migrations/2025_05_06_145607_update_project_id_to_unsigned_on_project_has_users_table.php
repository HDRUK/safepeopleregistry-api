<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop existing indexes or foreign keys first if needed
        Schema::table('project_has_users', function (Blueprint $table) {
            // If there are any foreign keys on project_id, drop them first
            // Example: $table->dropForeign(['project_id']);
            $table->dropIndex(['project_id']); // Drop index to change column
        });

        // Change the column type
        Schema::table('project_has_users', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->change();
        });

        // Recreate the index if needed
        Schema::table('project_has_users', function (Blueprint $table) {
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::table('project_has_users', function (Blueprint $table) {
            $table->dropIndex(['project_id']);
            $table->bigInteger('project_id')->change(); // revert back to signed
            $table->index('project_id');
        });
    }
};
