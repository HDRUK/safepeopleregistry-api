<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\State;

class OrgInProgressStateSeeder extends Seeder
{
    public function run(): void
    {
        State::updateOrCreate(
            ['slug' => State::STATE_ORG_IN_PROGRESS],
            [
                'name' => Str::studly(State::STATE_ORG_IN_PROGRESS),
            ]
        );
    }
}