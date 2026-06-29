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
        
        Schema::table('accreditations', function (Blueprint $table) {
            $table->string('associated_organisation_name')->nullable()->after('id');
            $table->string('id_string')->nullable()->after('associated_organisation_name');
            $table->date('issue_date')->nullable()->after('id_string');
            $table->date('expiry_date')->nullable()->after('issue_date');
            $table->dropColumn('awarded_at');
            $table->dropColumn('awarding_body_name');
            $table->dropColumn('awarding_body_ror');
            $table->dropColumn('title');
            $table->dropColumn('expires_at');
            $table->dropColumn('awarded_locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accreditations', function (Blueprint $table) {
            $table->dropColumn('associated_organisation_name');
            $table->dropColumn('id_string');
            $table->dropColumn('issue_date');
            $table->dropColumn('expiry_date');
            $table->string('awarded_at', 10)->default(''); // in form of MM/YYYY
            $table->string('awarding_body_name', 255)->default('');
            $table->string('awarding_body_ror', 255)->nullable()->default(null);
            $table->text('title');
            $table->string('expires_at', 10)->default(''); // in form of MM/YYYY
            $table->string('awarded_locale', 255)->default(''); // country
        });
    }
};
