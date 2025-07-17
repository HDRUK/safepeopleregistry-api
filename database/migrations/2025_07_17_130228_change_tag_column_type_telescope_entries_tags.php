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
            $table->dropForeign(['entry_uuid']);
        });

        Schema::table('telescope_entries_tags', function (Blueprint $table) {
            $table->dropPrimary(['entry_uuid', 'tag']);
            $table->dropIndex(['tag']);
        });

        Schema::table('telescope_entries_tags', function (Blueprint $table) {
            $table->mediumText('tag')->change();
        });

        Schema::table('telescope_entries_tags', function (Blueprint $table) {
            $table->primary('entry_uuid');
        });

        Schema::table('telescope_entries_tags', function (Blueprint $table) {
            $table->foreign('entry_uuid')
                ->references('uuid')
                ->on('telescope_entries')
                ->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('telescope_entries_tags', function (Blueprint $table) {
            $table->dropForeign(['entry_uuid']);
            $table->dropPrimary(['entry_uuid']);
        });

        Schema::table('telescope_entries_tags', function (Blueprint $table) {
            $table->string('tag', 500)->change();
        });

        Schema::table('telescope_entries_tags', function (Blueprint $table) {
            $table->primary(['entry_uuid', 'tag']);
            $table->index('tag');
            $table->foreign('entry_uuid')
                ->references('uuid')
                ->on('telescope_entries')
                ->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }
};
