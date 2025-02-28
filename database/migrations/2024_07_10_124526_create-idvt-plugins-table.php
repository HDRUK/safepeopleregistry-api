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
        Schema::create('idvt_plugins', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('function', 255);
            $table->string('args', 255);
            $table->text('config');
            $table->tinyInteger('enabled')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idvt_plugins');
    }
};
