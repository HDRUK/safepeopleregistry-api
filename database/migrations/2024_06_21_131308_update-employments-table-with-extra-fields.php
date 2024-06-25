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
        Schema::table('employments', function (Blueprint $table) {
            $table->string('from')->change();
            $table->string('to')->change();

            $table->string('department', 255)->nullable()->default(null);
            $table->string('role', 255)->default('');
            $table->mediumText('employer_address');
            $table->string('ror', 255)->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employments', function (Blueprint $table) {
            $table->dateTime('from')->change();
            $table->dateTime('to')->change();
            
            $table->dropColumn('department');
            $table->dropColumn('role');
            $table->dropColumn('employer_address');
            $table->dropColumn('ror');
        });
    }
};
