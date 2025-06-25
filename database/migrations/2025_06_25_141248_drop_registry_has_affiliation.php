<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class () extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('registry_has_affiliations');
    }

    public function down(): void
    {
        Schema::create('registry_has_affiliations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('affiliation_id');
            $table->bigInteger('registry_id');
        });
    }
};
