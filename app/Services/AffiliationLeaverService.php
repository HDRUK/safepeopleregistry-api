<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\State;
use App\Models\Affiliation;
use App\Models\CustodianUser;
use App\Models\CustodianHasProjectUser;
use App\Enums\AffiliationEndDateSource;
use App\Traits\UserLeftProjectWebhook;
use App\Traits\Notifications\NotificationUserManager;
use App\Notifications\Affiliations\AffiliationEnded;
use App\Notifications\Affiliations\AffiliationEndingSoon;
use App\Notifications\Affiliations\AffiliationEndDateRecorded;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use TriggerEmail;

/**
 * Records when a User leaves an Organisation, and ends the affiliation on that date.
 *
 * Nothing is deleted: the affiliation, and the projects the user was on through it, are kept as
 * history. Ending an affiliation takes the user off those projects, and their Custodians must then
 * confirm they have revoked the user's real data access.
 */
class AffiliationLeaverService
{
    use NotificationUserManager;
    use UserLeftProjectWebhook;

    /**
     * Records, changes or (with a null date) removes the affiliation's end date. A date of today or
     * earlier ends the affiliation straight away; a later one is ended by app:checking-affiliations.
     *
     * The caller must check the Organisation's date is not being overridden by the user.
     */
    public function recordEndDate(
        Affiliation $affiliation,
        ?string $endDate,
        User $actor,
        AffiliationEndDateSource $source
    ): Affiliation {
        $endDate = Affiliation::toDateString($endDate);
        $old = [
            'to' => $affiliation->to,
            'end_date_source' => $affiliation->end_date_source?->value,
        ];

        DB::transaction(function () use ($affiliation, $endDate, $actor, $source, $old) {
            $affiliation->to = $endDate;
            $affiliation->end_date_recorded_at = $endDate ? Carbon::now() : null;
            $affiliation->end_date_recorded_by = $endDate ? $actor->id : null;
            $affiliation->end_date_source = $endDate ? $source : null;

            // A new date deserves a new warning.
            if ($old['to'] !== $endDate) {
                $affiliation->end_date_warning_sent_at = null;
            }

            $affiliation->save();

            activity('affiliation')
                ->causedBy($actor)
                ->performedOn($affiliation)
                ->withProperties([
                    'id' => $affiliation->id,
                    'attributes' => [
                        'to' => $endDate,
                        'end_date_source' => $affiliation->end_date_source?->value,
                    ],
                    'old' => $old,
                ])
                ->event('updated')
                ->log('end date recorded');
        });

        $this->notifyEndDateRecorded($affiliation, $source);

        if ($endDate !== null && Carbon::parse($endDate)->lte(Carbon::today())) {
            $this->end($affiliation);
        }

        return $affiliation->refresh();
    }

    /**
     * Ends an affiliation whose end date has arrived. A user who never verified their professional
     * email is "never verified", everyone else has "left". Every project the user was on through
     * the affiliation is left, and its Custodians must confirm they have revoked access.
     *
     * Returns false, changing nothing, when the affiliation has already ended or was rejected.
     */
    public function end(Affiliation $affiliation): bool
    {
        $projectUsers = $this->endAffiliation($affiliation, true, 'affiliation ended');

        if ($projectUsers === null) {
            return false;
        }

        $affiliation->refresh();
        $this->notifyEnded($affiliation, $projectUsers);

        return true;
    }

    /**
     * Ends an affiliation whose end date passed before the leaver flow existed, such as one a user
     * added as historic. Its projects are left, but nobody is notified, no webhooks are sent and
     * Custodians are not asked to revoke access for what may be a years-old leaver.
     *
     * Returns false, changing nothing, when the affiliation has already ended or was rejected.
     */
    public function endLegacy(Affiliation $affiliation): bool
    {
        return $this->endAffiliation($affiliation, false, 'legacy affiliation ended') !== null;
    }

    /**
     * @return Collection<int, CustodianHasProjectUser>|null the project users who left, or null when nothing changed
     */
    private function endAffiliation(Affiliation $affiliation, bool $requireAccessRevocation, string $log): ?Collection
    {
        return DB::transaction(function () use ($affiliation, $requireAccessRevocation, $log): ?Collection {
            $affiliation = Affiliation::lockForUpdate()->with('modelState.state')->findOrFail($affiliation->id);
            $oldState = $affiliation->getState();

            if ($affiliation->hasEnded() || $oldState === State::STATE_AFFILIATION_REJECTED) {
                return null;
            }

            $newState = in_array($oldState, Affiliation::UNVERIFIED_STATES, true)
                ? State::STATE_AFFILIATION_NEVER_VERIFIED
                : State::STATE_AFFILIATION_LEFT;

            $affiliation->current_employer = false;
            $affiliation->left_at = Carbon::now();
            $affiliation->save();
            $affiliation->setState($newState);

            activity('affiliation')
                ->performedOn($affiliation)
                ->withProperties([
                    'id' => $affiliation->id,
                    'new' => $newState,
                    'old' => $oldState,
                    'to' => $affiliation->to,
                ])
                ->event('updated')
                ->log($log);

            return $this->leaveProjects($affiliation, $requireAccessRevocation);
        });
    }

    /**
     * Warns the user, once per end date, that their affiliation is ending soon.
     */
    public function warnEndingSoon(Affiliation $affiliation): void
    {
        $user = $this->getAffiliationUser($affiliation);

        $affiliation->end_date_warning_sent_at = Carbon::now();
        $affiliation->save();

        if (!$user) {
            return;
        }

        Notification::send($user, new AffiliationEndingSoon($affiliation));

        TriggerEmail::spawnEmail([
            'type' => 'AFFILIATION_ENDING_SOON_USER',
            'identifier' => 'affiliation_ending_soon_user',
            'to' => $user->id,
            'by' => -1,
            'affiliationId' => $affiliation->id,
        ]);
    }

    /**
     * Reminds the Custodian that a user's access to a project they have left is still to be revoked.
     */
    public function remindAccessRevocation(CustodianHasProjectUser $projectUser): void
    {
        $projectUser->access_revocation_reminded_at = Carbon::now();
        $projectUser->save();

        $projectHasUser = $projectUser->projectHasUser;
        $user = $projectHasUser->registry?->user;
        if (!$user) {
            return;
        }

        foreach ($this->getCustodianUsers($projectUser->custodian_id) as $custodianUser) {
            TriggerEmail::spawnEmail([
                'type' => 'AFFILIATION_ACCESS_REVOCATION_REMINDER',
                'identifier' => 'affiliation_access_revocation_reminder',
                'to' => $custodianUser->id,
                'by' => -1,
                'for' => $user->id,
                'affiliationId' => $projectHasUser->affiliation_id,
                'projectId' => $projectHasUser->project_id,
            ]);
        }
    }

    /**
     * Records that the Custodian has revoked the real data access of a user who left the project.
     *
     * Returns false, changing nothing, when no revocation is awaited.
     */
    public function confirmAccessRevoked(CustodianHasProjectUser $projectUser, User $actor): bool
    {
        return DB::transaction(function () use ($projectUser, $actor): bool {
            $projectUser = CustodianHasProjectUser::lockForUpdate()
                ->awaitingAccessRevocation()
                ->find($projectUser->id);

            if (!$projectUser) {
                return false;
            }

            $projectUser->access_revoked_at = Carbon::now();
            $projectUser->access_revoked_by = $actor->id;
            $projectUser->save();

            activity('custodian_has_project_has_user')
                ->causedBy($actor)
                ->performedOn($projectUser)
                ->withProperties([
                    'id' => $projectUser->id,
                    'project_has_user_id' => $projectUser->project_has_user_id,
                    'custodian_id' => $projectUser->custodian_id,
                ])
                ->event('updated')
                ->log('access revoked');

            return true;
        });
    }

    /**
     * Takes the user off every project they were on through the affiliation.
     *
     * @return Collection<int, CustodianHasProjectUser> the Custodians' records of each project left
     */
    private function leaveProjects(Affiliation $affiliation, bool $requireAccessRevocation): Collection
    {
        $projectUsers = CustodianHasProjectUser::with(['projectHasUser.project', 'modelState.state'])
            ->whereHas('projectHasUser', function ($query) use ($affiliation) {
                $query->where('affiliation_id', $affiliation->id);
            })
            ->get()
            ->reject(fn (CustodianHasProjectUser $projectUser) => $projectUser->getState() === State::STATE_USER_LEFT_PROJECT)
            ->values();

        foreach ($projectUsers as $projectUser) {
            $oldState = $projectUser->getState();

            $projectUser->setState(State::STATE_USER_LEFT_PROJECT);
            if ($requireAccessRevocation) {
                $projectUser->access_revocation_required_at = Carbon::now();
            }
            $projectUser->save();

            activity('project_has_user')
                ->performedOn($projectUser)
                ->withProperties([
                    'project_id' => $projectUser->projectHasUser->project_id,
                    'project_title' => $projectUser->projectHasUser->project?->title,
                    'affiliation_id' => $affiliation->id,
                    'new' => State::STATE_USER_LEFT_PROJECT,
                    'old' => $oldState,
                ])
                ->event('updated')
                ->log('user_left_project');
        }

        return $projectUsers;
    }

    private function notifyEndDateRecorded(Affiliation $affiliation, AffiliationEndDateSource $source): void
    {
        $user = $this->getAffiliationUser($affiliation);
        if (!$user) {
            return;
        }

        // Tell the other side; whoever recorded it already knows.
        if ($source === AffiliationEndDateSource::Organisation) {
            Notification::send($user, new AffiliationEndDateRecorded($user, $affiliation, 'user'));
            return;
        }

        $organisationUsers = $this->getAffiliationUserOrganisation($affiliation);
        if ($organisationUsers->isNotEmpty()) {
            Notification::send($organisationUsers, new AffiliationEndDateRecorded($user, $affiliation, 'organisation'));
        }
    }

    /**
     * @param Collection<int, CustodianHasProjectUser> $projectUsers
     */
    private function notifyEnded(Affiliation $affiliation, Collection $projectUsers): void
    {
        $user = $this->getAffiliationUser($affiliation);
        if (!$user) {
            return;
        }

        Notification::send($user, new AffiliationEnded($user, $affiliation, 'user'));
        TriggerEmail::spawnEmail([
            'type' => 'AFFILIATION_ENDED_USER',
            'identifier' => 'affiliation_ended_user',
            'to' => $user->id,
            'by' => -1,
            'affiliationId' => $affiliation->id,
        ]);

        // An Organisation never affiliated a user who never verified their email.
        if ($affiliation->getState() === State::STATE_AFFILIATION_LEFT) {
            $organisationUsers = $this->getAffiliationUserOrganisation($affiliation);
            if ($organisationUsers->isNotEmpty()) {
                Notification::send($organisationUsers, new AffiliationEnded($user, $affiliation, 'organisation'));
            }

            foreach ($organisationUsers as $organisationUser) {
                TriggerEmail::spawnEmail([
                    'type' => 'AFFILIATION_ENDED_ORGANISATION',
                    'identifier' => 'affiliation_ended_organisation',
                    'to' => $organisationUser->id,
                    'by' => -1,
                    'for' => $user->id,
                    'affiliationId' => $affiliation->id,
                ]);
            }
        }

        foreach ($projectUsers as $projectUser) {
            $project = $projectUser->projectHasUser->project;

            foreach ($this->getCustodianUsers($projectUser->custodian_id) as $custodianUser) {
                Notification::send($custodianUser, new AffiliationEnded($user, $affiliation, 'custodian', $project));
                TriggerEmail::spawnEmail([
                    'type' => 'AFFILIATION_ENDED_CUSTODIAN',
                    'identifier' => 'affiliation_ended_custodian',
                    'to' => $custodianUser->id,
                    'by' => -1,
                    'for' => $user->id,
                    'affiliationId' => $affiliation->id,
                    'projectId' => $project?->id,
                ]);
            }
        }

        foreach ($projectUsers->map(fn ($projectUser) => $projectUser->projectHasUser)->unique('project_id') as $projectHasUser) {
            $this->dispatchUserLeftProjectWebhooks($projectHasUser->project_id, $projectHasUser->user_digital_ident);
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function getCustodianUsers(int $custodianId): Collection
    {
        return User::query()
            ->where('user_group', User::GROUP_CUSTODIANS)
            ->whereIn('custodian_user_id', CustodianUser::where('custodian_id', $custodianId)->select('id'))
            ->get();
    }
}
