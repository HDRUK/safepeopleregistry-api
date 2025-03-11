<?php

namespace Database\Seeders;

use Str;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        State::truncate();
        Schema::enableForeignKeyConstraints();

        foreach (State::STATES as $s) {
            State::create([
                'name' => Str::studly(strtolower(str_replace('_', ' ', $s))),
                'slug' => $s,
            ]);
        }
    }
}
