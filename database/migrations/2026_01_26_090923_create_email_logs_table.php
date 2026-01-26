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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('to')->index();
            $table->string('subject');
            $table->string('type');
            $table->json('data');
            $table->string('template');
            $table->text('body');

            $table->string('job_uuid');
            $table->tinyInteger('job_status')->default(1);

            $table->string('message_id')->nullable();
            $table->string('message_status')->nullable();
            $table->text('message_response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
