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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('unique_id', 255);
            $table->text('title');
            $table->text('lay_summary');
            $table->text('public_benefit');
            $table->string('request_category_type', 255)->nullable()->default(null);
            $table->text('technical_summary')->nullable()->default(null);
            $table->string('other_approval_committees', 255)->nullable()->default(null);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('affiliate_id')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
