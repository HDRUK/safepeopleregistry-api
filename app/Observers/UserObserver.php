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
use App\Models\Affiliation;
use App\Models\Organisation;
use App\Models\PendingInvite;
use App\Traits\TracksModelChanges;
use App\Models\CustodianHasProjectUser;
use App\Notifications\AdminUserChanged;
use Illuminate\Support\Facades\Notification;
use App\Notifications\User\UpdateProfileDetails;

class UserObserver
{
    use TracksModelChanges;

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

        // $changes = $this->getUserTrackedChanges($user->getOriginal(), $user);
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
            $this->sendNotificationOnUpdate($user, $changes);
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

        // change state for affiliation
        $pendingInvites = PendingInvite::where([
            'user_id' => $user->id,
            'status' => PendingInvite::STATE_PENDING,
        ])->first();

        if ($user->user_group === User::GROUP_USERS &&
            $user->isDirty('unclaimed') && 
            $user->getOriginal('unclaimed') === 1 &&
            $user->unclaimed === 0 &&
            ($pendingInvites && in_array($pendingInvites->type, ['custodian_user_invite', 'organisation_user_invite']))) {

            $affiliations = Affiliation::where('registry_id', $user->registry_id)->first();
            if ($affiliations->getState() === State::STATE_AFFILIATION_INVITED) {
                \Log::info('affiliation state change for user claim - affiliation id: ' . $affiliations->id);
                $affiliations->setState(State::STATE_AFFILIATION_INFO_REQUIRED);
            }

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

    public function sendNotificationOnUpdate(User $user, array $changes)
    {
        if ($user->user_group === User::GROUP_USERS) {
            // current user
            Notification::send($user, new UpdateProfileDetails($user, $changes, 'user'));

            // organisation
            $organisations = User::where([
                'organisation_id' => $user->organisation_id,
                'user_group' => User::GROUP_ORGANISATIONS,
            ])->get();
            foreach ($organisations as $organisation) {
                Notification::send($organisation, new UpdateProfileDetails($user, $changes, 'organisation'));
            }

            // custodians
            $custodianIds = CustodianHasProjectUser::query()
                ->whereHas('projectHasUser.registry.user', function ($query) use ($user) {
                    $query->where('id', $user->id);
                })
                ->select('custodian_id')
                ->pluck('custodian_id')->toArray();

            foreach (array_unique($custodianIds) as $custodianId) {
                $custodian = User::where('custodian_user_id', $custodianId)->first();
                if ($custodian) {
                    Notification::send($custodian, new UpdateProfileDetails($user, $changes, 'custodian'));
                }
            }
        } else {
            $usersToNotify = $this->getOrganisationAdminUsers(
                [
                    $user->organisation_id,
                    $user->getOriginal('organisation_id')
                ]
            );

            Notification::send($usersToNotify, new AdminUserChanged($user, $changes));
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
