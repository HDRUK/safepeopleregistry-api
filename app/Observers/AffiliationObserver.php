<?php

namespace App\Observers;

use TriggerEmail;
use App\Models\User;
use App\Models\State;
use App\Models\Affiliation;
use App\Jobs\MergeUserAccounts;
use App\Traits\AffiliationCompletionManager;
use App\Traits\Notifications\NotificationUserManager;

class AffiliationObserver
{
    use AffiliationCompletionManager;
    use NotificationUserManager;

    public function created(Affiliation $affiliation): void
    {
        $this->setInitialState($affiliation);
        $this->handleChange($affiliation);
        $this->sendNotificationOnCreate($affiliation);
    }

    public function updated(Affiliation $affiliation): void
    {
        $this->handleChange($affiliation);
        $old = new Affiliation($affiliation->getOriginal());
        $this->sendNotificationOnUpdate($affiliation, $old);
    }

    public function deleted(Affiliation $affiliation): void
    {
        $this->handleChange($affiliation);
    }

    protected function handleChange(Affiliation $affiliation): void
    {
        $this->emailDelegatesIfNowComplete($affiliation);
        $this->updateActionLog($affiliation->registry_id);
        $this->updateOrganisationActionLog($affiliation);

        MergeUserAccounts::dispatch($affiliation);
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
            && filled($affiliation->from) && $affiliation->is_verified === 1;
    }

    private function sendDelegateEmails(Affiliation $affiliation): void
    {
        $userDelegates = User::where([
                'organisation_id' => $affiliation->organisation_id,
                'user_group' => 'ORGANISATIONS',
            ])
            ->where(function ($query) {
                $query->where('is_delegate', 1)
                    ->orWhere('is_sro', 1);
            })
            ->get();

        $userId = $affiliation->registry?->user?->id;
        if (!$userId) {
            return;
        }

        foreach ($userDelegates as $userDelegate) {
            $email = [
                'type' => 'DELEGATE_AFFILIATION_REQUEST',
                'to' => $userDelegate->id,
                'by' => $affiliation->organisation_id,
                'for' => $userId,
                'identifier' => 'delegate_affiliation_request',
                'affiliationId' => $affiliation->id,
            ];

            TriggerEmail::spawnEmail($email);

            $affiliation->setState(State::STATE_AFFILIATION_EMAIL_VERIFY);
        }
    }

    private function setInitialState(Affiliation $affiliation)
    {
        if (!$affiliation->getState()) {
            return $affiliation->setState(State::STATE_AFFILIATION_PENDING);
        }
    }

    public function sendNotificationOnCreate(Affiliation $affiliation): void
    {
        $this->notifyOnUserCreateAffiliation($affiliation);
    }

    public function sendNotificationOnUpdate(Affiliation $affiliation, $old): void
    {
        $this->notifyOnUserChangeAffiliation($affiliation, $old);
    }

}
