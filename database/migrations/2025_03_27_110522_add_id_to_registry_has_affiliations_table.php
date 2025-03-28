<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('registry_has_affiliations_temp', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('affiliation_id');
            $table->bigInteger('registry_id');
        });

        DB::statement('INSERT INTO registry_has_affiliations_temp (affiliation_id, registry_id) SELECT affiliation_id, registry_id FROM registry_has_affiliations');

        Schema::drop('registry_has_affiliations');

        Schema::rename('registry_has_affiliations_temp', 'registry_has_affiliations');
    }

    public function down(): void
    {
        Schema::table('registry_has_affiliations', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }
};
