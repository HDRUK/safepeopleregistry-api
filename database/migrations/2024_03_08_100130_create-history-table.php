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
        Schema::create('histories', function (Blueprint $table) {
            // Nullable in the instance that this historic entry
            // is made up of one or more items, but not exclusively
            // all.
            $table->id();
            $table->timestamps();
            $table->bigInteger('employment_id')->nullable();
            $table->bigInteger('endorsement_id')->nullable();
            $table->bigInteger('infringement_id')->nullable();
            $table->bigInteger('project_id')->nullable();
            $table->bigInteger('access_key_id')->nullable();
            $table->string('custodian_identifier', 255)->nullable();
            $table->text('ledger_hash'); // Not nullable to remain computable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
