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
        Schema::dropIfExists('affiliations');
        Schema::dropIfExists('employments'); // No going back from this.

        Schema::create('affiliations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('organisation_id');
            $table->string('member_id', 255);
            $table->string('relationship', 255)->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('department', 255)->nullable();
            $table->string('role', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('ror')->nullable();
            $table->bigInteger('registry_id');

            $table->index('organisation_id');
            $table->index('member_id');
            $table->index('email');
            $table->index('registry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No going back from this. Too great a change.
    }
};
