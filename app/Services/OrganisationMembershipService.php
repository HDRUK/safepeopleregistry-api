<?php

namespace App\Services;

use App\Models\Organisation;
use App\Models\User;
use App\Notifications\Organisations\OrganisationDelegates;
use App\RegistryManagementController\RegistryManagementController as RMC;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use TriggerEmail;

class OrganisationMembershipService
{
    /**
     * Detaches a delegate from the organisation, then notifies them and the remaining claimed members.
     *
     * Returns false, changing nothing, when the user is not a delegate of the organisation
     * (including when they were removed concurrently).
     */
    public function removeDelegate(Organisation $organisation, int $userId, User $actor): bool
    {
        // Mass update: User model events and observers do not fire, so the activity is logged explicitly.
        $removed = DB::transaction(function () use ($organisation, $userId, $actor): bool {
            $detached = User::delegates()
                ->whereKey($userId)
                ->where('organisation_id', $organisation->id)
                ->update(['organisation_id' => null]);

            if ($detached === 0) {
                return false;
            }

            activity('user')
                ->causedBy($actor)
                ->performedOn($organisation)
                ->withProperties([
                    'id' => $userId,
                    'attributes' => ['organisation_id' => null],
                    'old' => ['organisation_id' => $organisation->id],
                ])
                ->event('updated')
                ->log('delegate removed');

            return true;
        });

        // Notify only once the removal has committed.
        if ($removed) {
            $this->notifyDelegateRemoved($organisation, User::findOrFail($userId), $actor);
        }

        return $removed;
    }

    /**
     * Makes a claimed member of the organisation its SRO, handing over immediately: the current
     * SRO becomes a delegate, and an invited SRO keeps their invitation but will join as a delegate.
     *
     * Once committed, the new SRO and a replaced claimed SRO each get their own email, and the
     * organisation's other claimed members are told who the new SRO is. Re-appointing the
     * current SRO changes nothing and sends nothing.
     *
     * Returns false, changing nothing, when the user is not a claimed member of the organisation.
     */
    public function appointSro(Organisation $organisation, int $userId, User $actor): bool
    {
        /** @var SroHandover|false|null $handover false: refused; null: already the SRO, nothing changed. */
        $handover = DB::transaction(function () use ($organisation, $userId, $actor): SroHandover|false|null {
            $members = $this->lockMembers($organisation);

            $appointee = $members->first(
                fn (User $member) => $member->id === $userId && !$member->unclaimed
            );

            if ($appointee === null) {
                return false;
            }

            // Re-appointing the current SRO changes nothing, including any invited SRO.
            if ($appointee->is_sro) {
                return null;
            }

            return new SroHandover(
                $organisation,
                $appointee->id,
                $this->handOverSro($members, $appointee),
                $actor
            );
        });

        if ($handover === false) {
            return false;
        }

        // Notify only once the appointment has committed.
        if ($handover !== null) {
            $this->notifyNewSro($handover);
            $this->notifySroHandover($handover);
        }

        return true;
    }

    /**
     * Invites someone new to be the organisation's SRO. The current SRO keeps the role until the
     * invitee claims their account; any other invited SRO keeps their invitation but will join
     * as a delegate.
     *
     * Returns null, creating nothing, when the email already belongs to a user; an existing
     * member should be appointed with appointSro() instead.
     */
    public function inviteSro(
        Organisation $organisation,
        string $firstName,
        string $lastName,
        string $email,
        User $actor
    ): ?User {
        $invitee = DB::transaction(function () use ($organisation, $firstName, $lastName, $email, $actor): ?User {
            if (User::where('email', $email)->exists()) {
                return null;
            }

            $this->lockMembers($organisation)
                ->filter(fn (User $member) => $member->is_sro && $member->unclaimed)
                ->each(fn (User $invitedSro) => $this->demoteToDelegate($invitedSro));

            return RMC::createUnclaimedUser([
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $email,
                'user_group' => User::GROUP_ORGANISATIONS,
                'organisation_id' => $organisation->id,
                'is_sro' => 1,
                'is_delegate' => 0,
                'invited_by' => $actor->id,
            ], strictCreate: true);
        });

        // Email only once the invitee has been committed.
        if ($invitee !== null) {
            TriggerEmail::spawnEmail([
                'type' => 'SRO_INVITE',
                'identifier' => 'sro_invite',
                'to' => $invitee->id,
                'by' => $actor->id,
                'organisationId' => $organisation->id,
            ]);
        }

        return $invitee;
    }

    /**
     * The organisation's users, locked so concurrent SRO changes cannot leave two SROs.
     *
     * @return Collection<int, User>
     */
    private function lockMembers(Organisation $organisation): Collection
    {
        return User::query()
            ->where('organisation_id', $organisation->id)
            ->where('user_group', User::GROUP_ORGANISATIONS)
            ->lockForUpdate()
            ->get();
    }

    /**
     * Makes $newSro the only SRO among $members: everyone else with the role becomes a delegate,
     * and an invited SRO keeps their invitation but will join as a delegate.
     *
     * Returns the claimed SRO who lost the role, if any.
     *
     * @param Collection<int, User> $members
     */
    private function handOverSro(Collection $members, User $newSro): ?int
    {
        $replacedSroId = null;

        foreach ($members as $member) {
            if ($member->id === $newSro->id || !$member->is_sro) {
                continue;
            }

            if (!$member->unclaimed) {
                $replacedSroId = $member->id;
            }

            $this->demoteToDelegate($member);
        }

        // is_delegate is legacy but still read (e.g. SsoTenantController), so keep it consistent.
        $newSro->is_sro = true;
        $newSro->is_delegate = 0;
        $newSro->save();

        return $replacedSroId;
    }

    // Saved, not mass updated, so observers and the activity log see the change.
    private function demoteToDelegate(User $member): void
    {
        $member->is_sro = false;
        $member->is_delegate = 1;
        $member->save();
    }

    private function notifyNewSro(SroHandover $handover): void
    {
        TriggerEmail::spawnEmail([
            'type' => 'SRO_APPOINTED_USER',
            'identifier' => 'sro_appointed_user',
            'to' => $handover->newSroId,
            'by' => $handover->actor->id,
            'for' => $handover->newSroId,
            'organisationId' => $handover->organisation->id,
        ]);
    }

    /**
     * Tells a replaced claimed SRO, and the organisation's other claimed members, who the new SRO is.
     * The new SRO is not included: whether they need telling depends on how they got the role.
     */
    private function notifySroHandover(SroHandover $handover): void
    {
        $email = [
            'by' => $handover->actor->id,
            'for' => $handover->newSroId,
            'organisationId' => $handover->organisation->id,
        ];

        if ($handover->replacedSroId !== null) {
            TriggerEmail::spawnEmail([
                ...$email,
                'type' => 'SRO_REPLACED_USER',
                'identifier' => 'sro_replaced_user',
                'to' => $handover->replacedSroId,
            ]);
        }

        // The new SRO and the replaced SRO are emailed separately, if at all.
        $recipients = User::query()
            ->where('organisation_id', $handover->organisation->id)
            ->where('user_group', User::GROUP_ORGANISATIONS)
            ->where('unclaimed', 0)
            ->whereKeyNot(array_filter([$handover->newSroId, $handover->replacedSroId]))
            ->pluck('id');

        foreach ($recipients as $recipientId) {
            TriggerEmail::spawnEmail([
                ...$email,
                'type' => 'SRO_APPOINTED_ORGANISATION',
                'identifier' => 'sro_appointed_organisation',
                'to' => $recipientId,
            ]);
        }
    }

    private function notifyDelegateRemoved(Organisation $organisation, User $delegate, User $actor): void
    {
        Notification::send($delegate, new OrganisationDelegates($actor, $delegate, 'remove'));

        TriggerEmail::spawnEmail([
            'type' => 'DELEGATE_REMOVED_USER',
            'identifier' => 'delegate_removed_user',
            'to' => $delegate->id,
            'by' => $actor->id,
            'organisationId' => $organisation->id,
        ]);

        $recipients = User::query()
            ->where('organisation_id', $organisation->id)
            ->where('user_group', User::GROUP_ORGANISATIONS)
            ->where('unclaimed', 0)
            ->pluck('id');

        foreach ($recipients as $recipientId) {
            TriggerEmail::spawnEmail([
                'type' => 'DELEGATE_REMOVED_ORGANISATION',
                'identifier' => 'delegate_removed_organisation',
                'to' => $recipientId,
                'by' => $actor->id,
                'for' => $delegate->id,
                'organisationId' => $organisation->id,
            ]);
        }
    }
}
