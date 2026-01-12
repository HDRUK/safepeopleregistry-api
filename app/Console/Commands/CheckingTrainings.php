<?php

namespace App\Console\Commands;

use Config;
use Exception;
use TriggerEmail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use App\Models\CustodianHasProjectUser;
use Illuminate\Support\Facades\Notification;
use App\Notifications\User\ExpiresTrainings;
use Illuminate\Support\Facades\Log;

class CheckingTrainings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:checking-trainings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scheduler command to check trainings validity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warningTrainingsExpire();
        $this->trainingsExpire();
    }

    public function warningTrainingsExpire()
    {
        $warningTrainingExpireDays = Config::get('speedi.system.training_expire_days');

        try {
            $users = User::query()
                ->where([
                    'user_group' => User::GROUP_USERS,
                ])
                ->with([
                    'registry.trainings' => function ($query) use ($warningTrainingExpireDays) {
                        $query->whereRaw('DATE(expires_at) = DATE_ADD(CURDATE(), INTERVAL ' . $warningTrainingExpireDays . ' DAY)');
                    },
                    'registry.affiliations' => function ($query) {
                        $query->where('current_employer', 1);
                    },
                ])
                ->whereHas('registry.trainings', function ($query) use ($warningTrainingExpireDays) {
                    $query->whereRaw('DATE(expires_at) = DATE_ADD(CURDATE(), INTERVAL ' . $warningTrainingExpireDays . ' DAY)');
                })
                ->get();

            
            foreach ($users as $user) {
                $this->sendWarningTrainingsExpireEmailToUser($user);
                $this->info("checking warning trainings expire for user id {$user->id} :: done");
            }

            return Command::SUCCESS;            
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            $this->newLine();
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());


            Log::error('Command checking warning trainings expire', [
                'message' => $e->getMessage()
            ]);

            return Command::FAILURE;
        }
    }

    public function trainingsExpire()
    {
        try {
            $users = User::query()
                ->where([
                    'user_group' => User::GROUP_USERS,
                ])
                ->with([
                    'registry.trainings' => function ($query) {
                        $query->whereRaw('DATE(expires_at) = CURDATE()');
                    },
                    'registry.affiliations' => function ($query) {
                        $query->where('current_employer', 1);
                    },
                ])
                ->whereHas('registry.trainings', function ($query) {
                    $query->whereRaw('DATE(expires_at) = CURDATE()');
                })
                ->get();

            foreach ($users as $user) {
                $this->sendNotificationOnTrainingExpired($user);
                $this->info("checking trainings expire for user id {$user->id} :: done");
            }

            return Command::SUCCESS;            
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            $this->newLine();
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());


            Log::error('Command checking trainings expire', [
                'message' => $e->getMessage()
            ]);

            return Command::FAILURE;
        }
    }

    public function sendNotificationOnTrainingExpired($user): void
    {
        $trainings = $user->registry->trainings;
        $affiliations = $user->registry?->affiliations;

        foreach ($trainings as $training) {
            // user
            Notification::send($user, new ExpiresTrainings($user, $training, 'user'));
            $this->sendTrainingsExpireEmailToUser($user);

            // organisation
            if ($affiliations) {
                foreach ($affiliations as $affiliation) {
                    if ($affiliation->organisation) {
                        $userOrganisations = $this->getUserOrganisation($affiliation);
                        if ($userOrganisations->isNotEmpty()) {
                            Notification::send($userOrganisations, new ExpiresTrainings($user, $training, 'organisation'));
                            $this->sendTrainingExpireEmailToOrg($userOrganisations, $user);
                        }
                    }
                }
            }

            // custodian
            $userCustodainIds = $this->getUserCustodian($user);
            foreach (array_unique($userCustodainIds) as $custodianId) {
                $custodian = User::where('custodian_user_id', $custodianId)->first();
                if ($custodian) {
                    Notification::send($custodian, new ExpiresTrainings($user, $training, 'custodian'));
                    $this->sendTrainingExpireEmailToCustCust($custodian, $user);
                }
            }

        }
    }

    public function sendWarningTrainingsExpireEmailToUser($user): void
    {
        $email = [
            'type' => 'USER_WARNING_TRAINING_EXPIRE',
            'to' => $user->id,
            'by' => -1,
            'identifier' => 'user_training_expiry_warning'
        ];

        TriggerEmail::spawnEmail($email);
    }

    public function sendTrainingsExpireEmailToUser($user): void
    {
        $email = [
            'type' => 'USER_TRAINING_EXPIRED',
            'to' => $user->id,
            'by' => -1,
            'identifier' => 'user_training_expired'
        ];

        TriggerEmail::spawnEmail($email);
    }

    public function sendTrainingExpireEmailToOrg($userOrgs, $user): void
    {
        foreach ($userOrgs as $userOrg) {
            $email = [
                'type' => 'CUST_ORG_TRAINING_EXPIRED',
                'to' => $userOrg->id,
                'by' => -1,
                'userId' => $user->id,
                'identifier' => 'cust_org_training_expired'
            ];

            TriggerEmail::spawnEmail($email);
        }
    }

    public function sendTrainingExpireEmailToCustCust($userCust, $user): void
    {
        $email = [
            'type' => 'CUST_ORG_TRAINING_EXPIRED',
            'to' => $userCust->id,
            'by' => -1,
            'userId' => $user->id,
            'identifier' => 'cust_org_training_expired'
        ];

        TriggerEmail::spawnEmail($email);
    }

    private function getUserOrganisation($affiliation): Collection
    {
        $organisationId = $affiliation->organisation?->id;
        if (!$organisationId) {
            return collect();
        }

        $userDelegates = User::where([
            'organisation_id' => $organisationId,
            'user_group' => User::GROUP_ORGANISATIONS,
            'is_delegate' => 1,
        ])->get();

        if ($userDelegates->count()) {
            return $userDelegates;
        }

        $userSros = User::where([
            'organisation_id' => $organisationId,
            'user_group' => User::GROUP_ORGANISATIONS,
            'is_sro' => 1,
        ])->get();

        if ($userSros->count()) {
            return $userSros;
        }

        return collect();
    }

    private function getUserCustodian(User $user): array
    {
        return CustodianHasProjectUser::query()
            ->whereHas('projectHasUser.registry.user', function ($query) use ($user) {
                $query->where('id', $user->id);
            })
            ->select('custodian_id')
            ->pluck('custodian_id')->toArray();
    }
}
