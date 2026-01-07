<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Custodian;
use App\Models\EntityModelType;
use Illuminate\Console\Command;
use App\Models\CustodianHasProjectUser;
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
                $this->getUserById($custodianId, $userId);
            }

        }

        return Command::SUCCESS;
    }

    public function getUserById(int $cId, int $uId)
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

        dd([
            'uid' => $uId,
            // 'rules' => $decisionEvaluator->evaluate($user),
            'rules' => $rules,
        ]);

    }
}
