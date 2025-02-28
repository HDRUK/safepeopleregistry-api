<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('custodian_has_rules', function (Blueprint $table) {
            $table->unsignedBigInteger('custodian_id');
            $table->unsignedBigInteger('rule_id');

            $table->foreign('custodian_id')->references('id')->on('custodians')->onDelete('cascade');
            $table->foreign('rule_id')->references('id')->on('rules')->onDelete('cascade');

            $table->primary(['custodian_id', 'rule_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custodian_has_rules');
    }
};
