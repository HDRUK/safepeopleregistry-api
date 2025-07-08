<?php

namespace App\Observers;

use TriggerEmail;
use App\Models\User;
use App\Models\Affiliation;
use App\Models\State;
use App\Traits\AffiliationCompletionManager;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Affiliations\AffiliationCreated;
use App\Notifications\Affiliations\AffiliationDeleted;
use App\Notifications\Affiliations\AffiliationChanged;

class AffiliationObserver
{
    use AffiliationCompletionManager;

    public function created(Affiliation $affiliation): void
    {
        $this->setInitialState($affiliation);
        $this->handleChange($affiliation);
        $this->notifyAdmins(new AffiliationCreated(
            $this->getUser($affiliation),
            $affiliation
        ), $affiliation);
    }

    public function updated(Affiliation $affiliation): void
    {
        $this->handleChange($affiliation);
        $old = new Affiliation($affiliation->getOriginal());

        $this->notifyAdmins(new AffiliationChanged(
            $this->getUser($affiliation),
            $old,
            $affiliation
        ), $affiliation);
    }

    public function deleted(Affiliation $affiliation): void
    {
        $this->handleChange($affiliation);
        $this->notifyAdmins(new AffiliationDeleted(
            $this->getUser($affiliation),
            $affiliation
        ), $affiliation);
    }

    protected function handleChange(Affiliation $affiliation): void
    {

        // Not sure we should be ever sending delegates emails upon changes like this
        // - we have a notification system for it
        if (config('enable_email')) {
            $this->emailDelegatesIfNowComplete($affiliation);
        }
        $this->updateActionLog($affiliation->registry_id);
        $this->updateOrganisationActionLog($affiliation);
    }

    private function emailDelegatesIfNowComplete(Affiliation $affiliation): void
    {
        if (!$this->isNowComplete($affiliation)) {
            return;
        }

        if (!(app()->bound('seeding') && app()->make('seeding') === true)) {
            $this->sendDelegateEmails($affiliation);
        }
    }

    private function isNowComplete(Affiliation $affiliation): bool
    {
        return $this->checkComplete($affiliation)
            && !$this->checkComplete(new Affiliation($affiliation->getOriginal()));
    }

    private function checkComplete(Affiliation $affiliation): bool
    {
        return filled($affiliation->member_id)
            && filled($affiliation->relationship)
            && filled($affiliation->from);
    }

    private function sendDelegateEmails(Affiliation $affiliation): void
    {
        $delegateIds = User::where([
            'organisation_id' => $affiliation->organisation_id,
            'is_delegate' => 1
        ])->pluck('id');

        $userId = $affiliation->registry?->user?->id;
        if (!$userId) {
            return;
        }

        foreach ($delegateIds as $delegateId) {
            TriggerEmail::spawnEmail([
                'type' => 'USER_DELEGATE',
                'to' => $delegateId,
                'by' => $affiliation->organisation_id,
                'for' => $userId,
                'identifier' => 'delegate_sponsor'
            ]);
        }
    }

    private function setInitialState(Affiliation $affiliation): void
    {
        $unclaimed = $affiliation->organisation->unclaimed;
        $affiliation->setState($unclaimed
            ? State::STATE_AFFILIATION_INVITED
            : State::STATE_AFFILIATION_PENDING);
    }

    private function notifyAdmins($notification, Affiliation $affiliation): void
    {
        Notification::send($this->getOrgAdminsAndDelegates($affiliation), $notification);
    }

    private function getUser(Affiliation $affiliation): ?User
    {
        return $affiliation->registry?->user;
    }

    private function getOrgAdminsAndDelegates(Affiliation $affiliation)
    {
        return User::where([
            'organisation_id' => $affiliation->organisation->id,
            'user_group' => User::GROUP_ORGANISATIONS,
        ])->get();
    }
}
