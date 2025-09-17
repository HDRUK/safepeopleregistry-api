<?php

namespace App\Observers;

use Exception;
use Keycloak;
use App\Models\User;
use App\Models\State;
use App\Models\Organisation;
use App\Models\ActionLog;
use App\Jobs\OrcIDScanner;
use App\Models\DebugLog;
use App\Notifications\AdminUserChanged;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class UserObserver
{
    protected array $profileCompleteFields = [
        'first_name',
        'last_name',
        'email',
        'location'
    ];

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $user->setState(State::STATE_REGISTERED);

        foreach (User::getDefaultActions() as $action) {
            ActionLog::firstOrCreate([
                'entity_id' => $user->id,
                'entity_type' => User::class,
                'action' => $action,
            ], [
                'completed_at' => null,
            ]);
        }

        // Check the existence within keycloak.
        if ($user->unclaimed === 0) {
            if (!Keycloak::checkUserExists($user->id)) {
                // If not found, create and update local copy with keycloak id.

                $retVal = Keycloak::createUser([
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'group' => $user->user_group,
                    'id' => $user->id,
                ]);

                if (!$retVal['success']) {
                    throw new Exception('unable to clone user ' . json_encode($user) . ' within keycloak ');
                }
            }
        }

        // Call the OrcID scanner job to fetch the OrcID data.
        if ($user->consent_scrape) {
            OrcIDScanner::dispatch($user);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {

        DebugLog::create([
            'class' => User::class,
            'log' => 'User updated ::' . json_encode($user->getChanges()),
        ]);

        $changes = [];

        $fieldsToTrack = ['first_name', 'last_name', 'email'];

        foreach ($fieldsToTrack as $field) {
            if ($user->isDirty($field)) {
                $changes[$field] = [
                    'old' => $user->getOriginal($field),
                    'new' => $user->$field,
                ];
            }
        }

        if ($user->isDirty('organisation_id')) {
            $oldOrganisation = $user->getOriginal('organisation_id')
                ? Organisation::find($user->getOriginal('organisation_id'))
                : null;
            $newOrganisation = $user->organisation;

            $changes['organisation'] = [
                'old' => $oldOrganisation->organisation_name ?? 'N/A',
                'new' => $newOrganisation->organisation_name ?? 'N/A',
            ];
        }


        if (!empty($changes)) {
            $usersToNotify = $this->getOrganisationAdminUsers(
                [
                    $user->organisation_id,
                    $user->getOriginal('organisation_id')
                ]
            );

            Notification::send($usersToNotify, new AdminUserChanged($user, $changes));
        }

        if ($user->isDirty($this->profileCompleteFields)) {
            $isProfileComplete = collect($this->profileCompleteFields)
                ->every(fn ($field) => !empty($user->$field));

            if ($isProfileComplete) {
                ActionLog::updateOrCreate(
                    [
                        'entity_id' => $user->id,
                        'entity_type' => User::class,
                        'action' => User::ACTION_PROFILE_COMPLETED
                    ],
                    ['completed_at' => Carbon::now()]
                );
            }
        }

        // Call the OrcID scanner job to fetch the OrcID data.
        if ($user->consent_scrape) {
            OrcIDScanner::dispatch($user);
        }

    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }

    private function getOrganisationAdminUsers(array|int $orgId)
    {
        if (!is_array($orgId)) {
            $orgId = [$orgId];
        }
        return User::where('is_org_admin', 1)
            ->whereIn("organisation_id", $orgId)
            ->get();
    }
}
