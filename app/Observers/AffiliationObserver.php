<?php

namespace App\Observers;

use App\Jobs\MergeUserAccounts;
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
        $unclaimed = $affiliation->organisation->unclaimed;

        if ($unclaimed) {
            return $affiliation->setState(State::STATE_AFFILIATION_INVITED);
        }

        if (!$affiliation->current_employer) {
            return $affiliation->setState(State::STATE_AFFILIATION_PENDING);
        }

        if ($affiliation->current_employer == 1 && $affiliation->is_verified === 1) {
            return $affiliation->setState(State::STATE_AFFILIATION_PENDING);
        }

        if ($affiliation->current_employer == 1 && $affiliation->is_verified === 0) {
            return $affiliation->setState(State::STATE_AFFILIATION_EMAIL_VERIFY);
        }
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
