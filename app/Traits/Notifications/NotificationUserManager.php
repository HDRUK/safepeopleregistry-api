<?php

namespace App\Traits\Notifications;

use App\Models\User;
use App\Models\Affiliation;
use Illuminate\Support\Collection;
use App\Models\CustodianHasProjectUser;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Affiliations\AffiliationCreated;

trait NotificationUserManager
{
    public function notifyOnUserCreateAffiliation(Affiliation $affiliation)
    {
        $user = $this->getAffiliationUser($affiliation);
        if (!$user) {
            return;
        }

        $sendAffiliationNotification = false;
        if ($affiliation->current_employer) {
            $sendAffiliationNotification = true;
        }

        // user
        Notification::send($user, new AffiliationCreated($user, $affiliation, 'user'));
        if (!$sendAffiliationNotification) {
            Notification::send($user, new AffiliationCreated($user, $affiliation, 'user', $sendAffiliationNotification));
        }

        // organisation
        foreach ($this->getAffiliationUserOrganisation($affiliation) as $organisation) {
            Notification::send($organisation, new AffiliationCreated($user, $affiliation, 'organisation'));
            if (!$sendAffiliationNotification) {
                Notification::send($organisation, new AffiliationCreated($user, $affiliation, 'organisation', $sendAffiliationNotification));
            }
        }

        // custodian
        foreach (array_unique($this->getAffiliationUserCustodian($affiliation)) as $custodianId) {
            $custodian = User::where('custodian_user_id', $custodianId)->first();
            if ($custodian) {
                Notification::send($custodian, new AffiliationCreated($user, $affiliation, 'custodian'));
                if (!$sendAffiliationNotification) {
                    Notification::send($custodian, new AffiliationCreated($user, $affiliation, 'custodian', $sendAffiliationNotification));
                }
            }
        }
    }

    public function notifyOnUserChangeAffiliation(Affiliation $affiliation)
    {
        
    }

    public function getAffiliationUser(Affiliation $affiliation): ?User
    {
        return $affiliation->registry?->user;
    }

    private function getAffiliationUserOrganisation(Affiliation $affiliation): Collection
    {
        $userDelegates = User::where([
            'organisation_id' => $affiliation->organisation->id,
            'user_group' => User::GROUP_ORGANISATIONS,
            'is_delegate' => 1,
        ])->get();

        if ($userDelegates->count()) {
            return $userDelegates;
        }

        $userSros = User::where([
            'organisation_id' => $affiliation->organisation->id,
            'user_group' => User::GROUP_ORGANISATIONS,
            'is_sro' => 1,
        ])->get();

        if ($userSros->count()) {
            return $userSros;
        }

        return collect();
    }

    private function getAffiliationUserCustodian(Affiliation $affiliation): array
    {
        return CustodianHasProjectUser::query()
            ->whereHas('projectHasUser.registry.user', function ($query) use ($affiliation) {
                $query->where('id', $affiliation->registry?->user->id);
            })
            ->select('custodian_id')
            ->pluck('custodian_id')->toArray();
    }
}