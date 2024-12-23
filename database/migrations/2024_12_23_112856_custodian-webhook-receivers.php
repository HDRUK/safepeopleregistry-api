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
        Schema::create('custodian_webhook_receivers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('custodian_id');
            $table->text('url');
            $table->integer('webhook_event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custodian_webhook_receivers');
    }
};
