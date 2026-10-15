<?php

namespace App\Services;

use App\Models\Organisation;
use App\Models\User;
use App\Notifications\Organisations\OrganisationDelegates;
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
     * current SRO sends nothing.
     *
     * Returns false, changing nothing, when the user is not a claimed member of the organisation.
     */
    public function appointSro(Organisation $organisation, int $userId, User $actor): bool
    {
        /** @var array{sroChanged: bool, replacedSroId: ?int}|null $outcome */
        $outcome = DB::transaction(function () use ($organisation, $userId): ?array {
            // Locked so concurrent appointments cannot leave the organisation with two SROs.
            $members = User::query()
                ->where('organisation_id', $organisation->id)
                ->where('user_group', User::GROUP_ORGANISATIONS)
                ->lockForUpdate()
                ->get();

            $appointee = $members->first(
                fn (User $member) => $member->id === $userId && !$member->unclaimed
            );

            if ($appointee === null) {
                return null;
            }

            $sroChanged = !$appointee->is_sro;
            $replacedSroId = null;

            // Saved one by one, not mass updated, so observers and the activity log see each change.
            foreach ($members as $member) {
                if ($member->id === $appointee->id || !$member->is_sro) {
                    continue;
                }

                if (!$member->unclaimed) {
                    $replacedSroId = $member->id;
                }

                $member->is_sro = false;
                $member->is_delegate = 1;
                $member->save();
            }

            // is_delegate is legacy but still read (e.g. SsoTenantController), so keep it consistent.
            $appointee->is_sro = true;
            $appointee->is_delegate = 0;
            $appointee->save();

            return ['sroChanged' => $sroChanged, 'replacedSroId' => $replacedSroId];
        });

        if ($outcome === null) {
            return false;
        }

        // Notify only once the appointment has committed.
        if ($outcome['sroChanged']) {
            $this->notifySroAppointed($organisation, $userId, $outcome['replacedSroId'], $actor);
        }

        return true;
    }

    private function notifySroAppointed(Organisation $organisation, int $sroId, ?int $replacedSroId, User $actor): void
    {
        if ($replacedSroId !== null) {
            TriggerEmail::spawnEmail([
                'type' => 'SRO_REPLACED_USER',
                'identifier' => 'sro_replaced_user',
                'to' => $replacedSroId,
                'by' => $actor->id,
                'for' => $sroId,
                'organisationId' => $organisation->id,
            ]);
        }

        TriggerEmail::spawnEmail([
            'type' => 'SRO_APPOINTED_USER',
            'identifier' => 'sro_appointed_user',
            'to' => $sroId,
            'by' => $actor->id,
            'for' => $sroId,
            'organisationId' => $organisation->id,
        ]);

        // The new SRO and the replaced SRO have had their own emails.
        $recipients = User::query()
            ->where('organisation_id', $organisation->id)
            ->where('user_group', User::GROUP_ORGANISATIONS)
            ->where('unclaimed', 0)
            ->whereKeyNot(array_filter([$sroId, $replacedSroId]))
            ->pluck('id');

        foreach ($recipients as $recipientId) {
            TriggerEmail::spawnEmail([
                'type' => 'SRO_APPOINTED_ORGANISATION',
                'identifier' => 'sro_appointed_organisation',
                'to' => $recipientId,
                'by' => $actor->id,
                'for' => $sroId,
                'organisationId' => $organisation->id,
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
