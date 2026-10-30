<?php

use App\Models\DecisionModelType;
use App\Rules\Organisations\SroDeclarationCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the SRO declaration rule to environments already seeded by RulesSeeder, active for every custodian.
 * A fresh database gets it from RulesSeeder instead, as the decision model types are not seeded yet.
 */
return new class () extends Migration {
    public function up(): void
    {
        $typeId = DB::table('decision_model_types')
            ->where('name', DecisionModelType::ORG_VALIDATION_RULES)
            ->value('id');

        if ($typeId === null || DB::table('decision_models')->where('rule_class', SroDeclarationCheck::class)->exists()) {
            return;
        }

        $decisionModelId = DB::table('decision_models')->insertGetId([
            'name' => 'SRO declaration',
            'model_type' => \App\Models\Organisation::class,
            'conditions' => json_encode([
                'path' => 'files',
                'file_type' => \App\Models\File::FILE_TYPE_DECLARATION_SRO,
            ]),
            'rule_class' => SroDeclarationCheck::class,
            'description' => 'An Organisation has uploaded an SRO declaration since its current SRO was appointed.',
            'decision_model_type_id' => $typeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $configs = DB::table('custodians')->pluck('id')->map(fn ($custodianId) => [
            'custodian_id' => $custodianId,
            'decision_model_id' => $decisionModelId,
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        DB::table('custodian_model_configs')->insert($configs);
    }

    public function down(): void
    {
        $decisionModelIds = DB::table('decision_models')
            ->where('rule_class', SroDeclarationCheck::class)
            ->pluck('id');

        DB::table('decision_model_logs')->whereIn('decision_model_id', $decisionModelIds)->delete();
        DB::table('custodian_model_configs')->whereIn('decision_model_id', $decisionModelIds)->delete();
        DB::table('decision_models')->whereIn('id', $decisionModelIds)->delete();
    }
};
