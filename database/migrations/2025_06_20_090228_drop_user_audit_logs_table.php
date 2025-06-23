<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUserAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('user_audit_logs');
    }

    public function down()
    {
        Schema::create('user_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('class');
            $table->text('log');
            $table->timestamps();
        });
    }
}
