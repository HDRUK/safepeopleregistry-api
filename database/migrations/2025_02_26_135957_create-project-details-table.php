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
        Schema::create('project_details', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('project_id');
            $table->json('datasets')->nullable();
            $table->json('other_approval_committees')->nullable();
            $table->string('data_sensitivity_level')->nullable();
            $table->text('legal_basis_for_data_article6')->nullable();
            $table->tinyInteger('duty_of_confidentiality')->default(0);
            $table->tinyInteger('national_data_optout')->default(0);
            $table->enum('request_frequency', ['ONE-OFF', 'RECURRING'])->nullable();
            $table->text('dataset_linkage_description')->nullable();
            $table->text('data_minimisation')->nullable();
            $table->text('data_use_description')->nullable();
            $table->string('access_date')->nullable();

            $table->index('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_details');
    }
};
