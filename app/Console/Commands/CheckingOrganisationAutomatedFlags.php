<?php

namespace App\Console\Commands;

use App\Models\Custodian;
use App\Models\Organisation;
use App\Models\EntityModelType;
use Illuminate\Console\Command;
use App\Models\DecisionModelLog;
use App\Services\DecisionEvaluatorService;
use App\Models\CustodianHasProjectOrganisation;

class CheckingOrganisationAutomatedFlags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:checking-organisation-automated-flags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command checking organisation automated flags';

    protected $decisionEvaluator = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $custodianIds = Custodian::query()
            ->pluck('id')
            ->toArray();

        foreach ($custodianIds as $custodianId) {
            $organisationIds = CustodianHasProjectOrganisation::query()
                ->where([
                    'custodian_id' => $custodianId,
                ])
                ->with([
                    'projectOrganisation.organisation'
                ])
                ->get()
                ->map(fn ($item) => $item->projectOrganisation?->organisation?->id)
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            foreach ($organisationIds as $organisationId) {
                $this->checkOrganisationById($custodianId, $organisationId);
                $this->info("checking rules for organisation id :: {$organisationId} :: done");
            }
        }

        return Command::SUCCESS;
    }

    public function checkOrganisationById(int $cId, int $oId)
    {
        $this->decisionEvaluator = new DecisionEvaluatorService([EntityModelType::ORG_VALIDATION_RULES], $cId);

        $organisation = Organisation::with([
                'departments',
                'subsidiaries',
                'permissions',
                'ceExpiryEvidence',
                'cePlusExpiryEvidence',
                'isoExpiryEvidence',
                'dsptkExpiryEvidence',
                'charities',
                'registries',
                'registries.user',
                'registries.user.permissions',
                'sector',
                'files',
            ])->where('id', $oId)->first();
        $rules = $this->decisionEvaluator->evaluate($organisation);

        foreach ($rules as $rule) {
            DecisionModelLog::updateOrCreate([
                'decision_model_id' => $rule['ruleId'],
                'custodian_id' => $cId,
                'subject_id' => $oId,
                'model_type' => 'Organisation',
            ], [
                'status' => $rule['status'],
            ]);
        }
    }
}
