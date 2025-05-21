<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Custodian;
use App\Models\ValidationCheck;

class CustodianHasValidationCheckSeeder extends Seeder
{
    public function run(): void
    {
        $validationCheckIds = ValidationCheck::pluck('id')->all();

        foreach (Custodian::all() as $custodian) {
            $custodian->validationChecks()->syncWithoutDetaching($validationCheckIds);
        }
    }
}
