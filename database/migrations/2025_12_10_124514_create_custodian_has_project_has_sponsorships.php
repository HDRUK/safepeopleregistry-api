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
        Schema::create('custodian_has_project_has_sponsorships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_has_sponsorship_id');
            $table->unsignedBigInteger('custodian_id');

            $table->foreign('project_has_sponsorship_id')->references('id')->on('project_has_sponsorships')->onDelete('cascade');
            $table->foreign('custodian_id')->references('id')->on('custodians')->onDelete('cascade');

            $table->index(['project_has_sponsorship_id', 'custodian_id']);
            $table->unique(['project_has_sponsorship_id', 'custodian_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custodian_has_project_has_sponsorships');
    }
};
