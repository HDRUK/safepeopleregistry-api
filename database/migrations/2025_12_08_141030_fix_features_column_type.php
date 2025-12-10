<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('features', function (Blueprint $table) {
            $table->text('value')->change();
        });


        DB::table('features')->update([
            'value' => DB::raw("
                CASE 
                    WHEN value = 1 THEN 'true'
                    WHEN value = 0 THEN 'false'
                    ELSE value
                END
            ")
        ]);
    }

    public function down()
    {
        DB::table('features')->update([
            'value' => DB::raw("
                CASE
                    WHEN value = 'true' THEN 1
                    WHEN value = 'false' THEN 0
                    ELSE value
                END
            ")
        ]);

        Schema::table('features', function (Blueprint $table) {
            $table->tinyInteger('value')->change();
        });
    }
};
