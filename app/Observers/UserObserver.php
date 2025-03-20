<?php

namespace App\Observers;

use App\Models\User;
use App\Models\State;
use App\Models\Organisation;
use App\Models\ActionLog;
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
            ActionLog::create([
                'entity_id' => $user->id,
                'entity_type' => User::class,
                'action' => $action,
                'completed_at' => null,
            ]);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $originalUser = $user->getOriginal();
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
                'old' => $oldOrganisation?->organisation_name ?? 'N/A',
                'new' => $newOrganisation?->organisation_name ?? 'N/A',
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
