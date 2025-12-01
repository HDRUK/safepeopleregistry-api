<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use App\Models\CustodianHasProjectUser;
use Illuminate\Support\Facades\Notification;
use App\Notifications\User\ExpiresCertifications;

class CheckingCertifcates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:checking-certifcates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scheduler command to check certificates validity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \DB::enableQueryLog();

        $users = User::query()
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
            $this->sendNotificationOnExpires($user);
        }

        return Command::SUCCESS;
    }

    public function sendNotificationOnExpires($user): void
    {
        $trainings = $user->registry->trainings;
        $affiliations = $user->registry?->affiliations;

        foreach ($trainings as $training) {
            // user
            Notification::send($user, new ExpiresCertifications($user, $training, 'user'));

            // organisation
            if ($affiliations) {
                foreach ($affiliations as $affiliation) {
                    if ($affiliation->organisation) {
                        $userOrganisations = $this->getUserOrganisation($affiliation);
                        foreach ($userOrganisations as $userOrganisation) {
                            Notification::send($userOrganisation, new ExpiresCertifications($user, $training, 'organisation'));
                        }
                    }
                }
            }

            // custodian
            $userCustodainIds = $this->getUserCustodian($user);
            foreach (array_unique($userCustodainIds) as $custodianId) {
                $custodian = User::where('custodian_user_id', $custodianId)->first();
                if ($custodian) {
                    Notification::send($custodian, new ExpiresCertifications($user, $training, 'custodian'));
                }
            }

        }
    }

    private function getUserOrganisation($affiliation): Collection
    {
        return User::where([
            'organisation_id' => $affiliation->organisation?->id,
            'user_group' => User::GROUP_ORGANISATIONS,
        ])->get();
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
