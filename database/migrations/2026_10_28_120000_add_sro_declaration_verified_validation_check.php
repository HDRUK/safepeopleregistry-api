<?php

use App\Models\Custodian;
use App\Models\Organisation;
use App\Models\ValidationCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the SRO declaration manual check to environments already seeded by ValidationCheckSeeder, as
 * the default and for every custodian. Created through the model, as the seeder and API do, so
 * ValidationCheckObserver gives each custodian a pending check for every organisation.
 */
return new class () extends Migration {
    private const NAME = 'sro_declaration_verified';

    public function up(): void
    {
        $check = collect(Organisation::defaultValidationChecks())->firstWhere('name', self::NAME);

        foreach ([null, ...Custodian::pluck('id')] as $custodianId) {
            ValidationCheck::firstOrCreate(
                [
                    'name' => $check['name'],
                    'applies_to' => Organisation::class,
                    'custodian_id' => $custodianId,
                ],
                [
                    'description' => $check['description'],
                    'enabled' => 1,
                ]
            );
        }
    }

    public function down(): void
    {
        $checkIds = DB::table('validation_checks')
            ->where('name', self::NAME)
            ->where('applies_to', Organisation::class)
            ->pluck('id');

        // Comments on these logs are removed with them.
        DB::table('validation_logs')->whereIn('validation_check_id', $checkIds)->delete();
        DB::table('validation_checks')->whereIn('id', $checkIds)->delete();
    }
};
