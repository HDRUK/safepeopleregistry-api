<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Custodian;
use Illuminate\Support\Arr;
use App\Models\EntityModelType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Models\CustodianHasProjectUser;
use App\Models\DecisionModelLog;
use App\Models\Notification;
use App\Services\DecisionEvaluatorService;

class CheckingUserAutomatedFlags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:checking-user-automated-flags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command checking user automated flags';

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
            $userIds = CustodianHasProjectUser::query()
                ->where([
                    'custodian_id' => $custodianId,
                ])
                ->with([
                    'projectHasUser.registry.user'
                ])
                ->get()
                ->map(fn($item) => $item->projectHasUser?->registry?->user?->id)
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            foreach ($userIds as $userId) {
                $this->checkUserById($custodianId, $userId);
                $this->info("checking rules for user id :: {$userId} :: done");
            }

        }

        return Command::SUCCESS;
    }

    public function checkUserById(int $cId, int $uId)
    {
        $this->decisionEvaluator = new DecisionEvaluatorService([EntityModelType::USER_VALIDATION_RULES], $cId);

        $user = User::with([
                'permissions',
                'registry',
                'registry.files',
                'registry.affiliations',
                'pendingInvites',
                'organisation',
                'departments',
                'registry.identity',
                'registry.education',
                'registry.trainings',
            ])->where('id', $uId)->first();
        $rules = $this->decisionEvaluator->evaluate($user);

        foreach ($rules as $rule) {
            DecisionModelLog::updateOrCreate([
                'decision_model_id' => $rule['ruleId'],
                'custodian_id' => $cId,
                'subject_id' => $uId,
                'model_type' => 'User',
            ],[
                'status' => $rule['status'],
            ]);
        }
    }
}
