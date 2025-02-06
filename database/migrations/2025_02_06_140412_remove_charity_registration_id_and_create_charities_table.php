<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('charities', function (Blueprint $table) {
            $table->id();
            $table->string('registration_id')->unique();
            $table->string('name');
            $table->string('website')->nullable();
            $table->string('address_1', 255)->nullable();
            $table->string('address_2', 255)->nullable();
            $table->string('town', 255)->nullable();
            $table->string('county', 255)->nullable();
            $table->string('country', 255)->nullable();
            $table->string('postcode', 8)->nullable();
        });


        Schema::create('organisation_has_charity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisations')->onDelete('cascade');
            $table->foreignId('charity_id')->constrained('charities')->onDelete('cascade');
        });


        DB::table('organisations')->whereNotNull('charity_registration_id')->get()->each(function ($organisation) {
            $charity = DB::table('charities')->where('registration_id', $organisation->charity_registration_id)->first();

            if (!$charity) {
                $charityId = DB::table('charities')->insertGetId([
                    'registration_id' => $organisation->charity_registration_id,
                    'name' => 'Unknown',
                ]);
            } else {
                $charityId = $charity->id;
            }

            DB::table('organisation_has_charity')->insert([
                'organisation_id' => $organisation->id,
                'charity_id' => $charityId,
            ]);
        });

        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('charity_registration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->string('charity_registration_id')->nullable();
        });

        Schema::dropIfExists('organisation_has_charity');

        Schema::dropIfExists('charities');
    }
};
