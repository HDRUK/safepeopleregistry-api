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
        Schema::create('affiliations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('organisation_id');
            $table->string('member_id', 255);
            $table->bigInteger('current_employer')->default(0);
            $table->string('relationship', 255)->nullable();
        });

        Schema::create('registry_has_affiliations', function (Blueprint $table) {
            $table->bigInteger('affiliation_id');
            $table->bigInteger('registry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliations');
        Schema::dropIfExists('registry_has_affiliations');
    }
};
