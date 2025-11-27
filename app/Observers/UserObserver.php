<?php

namespace App\Observers;

use Keycloak;
use Exception;
use TriggerEmail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\State;
use App\Models\DebugLog;
use App\Models\ActionLog;
use App\Jobs\OrcIDScanner;
use App\Models\Organisation;
use App\Models\CustodianHasProjectUser;
use App\Notifications\UserUpdateProfile;
use Illuminate\Support\Facades\Notification;

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
        if (!$user->getState()) {
            $user->setState(State::STATE_INVITED);
        }

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

        if ($user->consent_scrape && filled($user->orc_id)) {
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

        $fieldsToTrack = ['first_name', 'last_name', 'email', 'role', 'location'];

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
            $this->sendNotificationUpdate($user, $changes);
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

        // send email to admin if user->is_sro
        if ($user->is_sro && !empty($changes)) {
            Organisation::where('id', $user->organisation_id)->update([
                'system_approved' => 0,
            ]);
            $userAdmins = User::where('user_group', User::GROUP_ADMINS)->select(['id'])->get();
            foreach ($userAdmins as $userAdmin) {
                $input = [
                    'type' => 'ORGANISATION_NEEDS_CONFIRMATION',
                    'to' => $user->organisation_id,
                    'by' => $userAdmin->id,
                    'identifier' => 'organisation_confirmation_needed'
                ];
                TriggerEmail::spawnEmail($input);
            }
        }

        if ($user->consent_scrape && filled($user->orc_id) && ($user->isDirty('orc_id') || $user->isDirty('consent_scrape'))) {
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

    public function sendNotificationUpdate(User $user, array $changes)
    {
        if ($user->user_group !== User::GROUP_USERS) {
            return;
        }

        // current user
        Notification::send($user, new UserUpdateProfile($user, $changes, 'user'));

        // organisation
        $organisation = Organisation::where('id', $user->organisation_id)->first();
        if (!is_null($organisation)) {
            Notification::send($organisation, new UserUpdateProfile($user, $changes, 'orgasnisation'));
        }
        
        // custodians
        $custodianIds = CustodianHasProjectUser::query()
            ->whereHas('projectHasUser.registry.user', function ($query) use ($user) {
                $query->where('id', $user->id);
            })
            ->select('custodian_id')
            ->pluck('custodian_id')->toArray();
        
        foreach (array_unique($custodianIds) as $custodianId) {
            $custodianUser = User::where('custodian_user_id', $custodianId)->first();
            if ($custodianUser) {
                Notification::send($custodianUser, new UserUpdateProfile($user, $changes, 'custodian'));
            }
        }
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
