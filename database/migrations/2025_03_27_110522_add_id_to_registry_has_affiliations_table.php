<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\RegistryHasAffiliation;

return new class () extends Migration {
    public function up(): void
    {
        $existingData = RegistryHasAffiliation::get()->toArray();

        Schema::drop('registry_has_affiliations');

        Schema::create('registry_has_affiliations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('affiliation_id');
            $table->bigInteger('registry_id');
        });

        DB::transaction(function () use ($existingData) {
            foreach ($existingData as $data) {
                // needed to create index
                // also needed to trigger observers that create new states
                RegistryHasAffiliation::create([
                    'affiliation_id' => $data['affiliation_id'],
                    'registry_id' => $data['registry_id'],
                ]);
            }
            $newData = RegistryHasAffiliation::get()->toArray();
        });


    }

    public function down(): void
    {
        Schema::table('registry_has_affiliations', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }
};
