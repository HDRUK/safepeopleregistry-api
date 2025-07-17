<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('telescope_entries_tags', function (Blueprint $table) {
            $table->string('tag', 500)->change();
        });

        DB::statement('CREATE INDEX telescope_tag_index ON telescope_entries_tags (tag(191))');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('telescope_entries_tags', function (Blueprint $table) {
            $table->string('tag')->change();
        });

        Schema::enableForeignKeyConstraints();
    }
};
