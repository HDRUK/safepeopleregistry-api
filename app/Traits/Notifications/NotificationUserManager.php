<?php

namespace App\Traits\Notifications;

use App\Models\User;
use App\Models\Affiliation;
use App\Models\CustodianUser;
use App\Models\DecisionModel;
use Illuminate\Support\Collection;
use App\Models\CustodianHasProjectUser;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserChangeAutomatedFlags;
use App\Notifications\Affiliations\AffiliationChanged;
use App\Notifications\Affiliations\AffiliationCreated;
use App\Models\Custodian;

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
        Notification::send($user, new AffiliationCreated($user, $affiliation, 'user', $sendAffiliationNotification));

        // organisation
        $userOrganisations = $this->getAffiliationUserOrganisation($affiliation);
        if ($userOrganisations->isNotEmpty()) {
            foreach ($userOrganisations as $userOrganisation) {
                Notification::send($userOrganisation, new AffiliationCreated($user, $affiliation, 'organisation', $sendAffiliationNotification));
            }
        }

        // custodian
        foreach ($this->getAffiliationUserCustodian($affiliation) as $custodianId) {
            $custodian = User::where('id', $custodianId)->first();
            if ($custodian) {
                Notification::send($custodian, new AffiliationCreated($user, $affiliation, 'custodian', $sendAffiliationNotification));
            }
        }
    }

    public function notifyOnUserChangeAffiliation(Affiliation $affiliation, $old)
    {
        $sendAffiliationNotification = false;
        if ($affiliation->current_employer && $old->current_employer !== $affiliation->current_employer) {
            $sendAffiliationNotification = true;
        }

        $user = $this->getAffiliationUser($affiliation);
        if (!$user) {
            return;
        }

        // user
        Notification::send($user, new AffiliationChanged($user, $old, $affiliation, 'user', $sendAffiliationNotification));

        // organisation
        $userOrganisations = $this->getAffiliationUserOrganisation($affiliation);
        if ($userOrganisations->isNotEmpty()) {
            foreach ($userOrganisations as $userOrganisation) {
                Notification::send($userOrganisation, new AffiliationChanged($user, $old, $affiliation, 'organisation', $sendAffiliationNotification));
            }
        }

        // custodian
        foreach ($this->getAffiliationUserCustodian($affiliation) as $custodianId) {
            $custodian = User::where('id', $custodianId)->first();
            if ($custodian) {
                Notification::send($custodian, new AffiliationChanged($user, $old, $affiliation, 'custodian', $sendAffiliationNotification));
            }
        }
    }

    public function notifyOnUserChangeAutomatedFlags($decisionModelLog)
    {
        $userId = $decisionModelLog->subject_id;
        $user = User::where('id', $userId)->first();
        $custodianId = $decisionModelLog->custodian_id;
        $userCustodian = User::where([
            'custodian_user_id' => $custodianId,
            'user_group' => User::GROUP_CUSTODIANS,
        ])->first();
        $custodian = Custodian::where('id', $custodianId)->first();
        $ruleId = $decisionModelLog->decision_model_id;
        $decisionModel = DecisionModel::where('id', $ruleId)->first();

        Notification::send($userCustodian, new UserChangeAutomatedFlags($user, $custodian, $decisionModel));
    }

    public function getAffiliationUser(Affiliation $affiliation): ?User
    {
        return $affiliation->registry?->user;
    }

    private function getAffiliationUserOrganisation(Affiliation $affiliation): Collection
    {
        $organisationId = $affiliation->organisation?->id;
        if (!$organisationId) {
            return collect();
        }

        $userDelegates = User::where([
            'organisation_id' => $organisationId,
            'user_group' => User::GROUP_ORGANISATIONS,
            'is_delegate' => 1,
        ])->get();

        if ($userDelegates->count()) {
            return $userDelegates;
        }

        $userSros = User::where([
            'organisation_id' => $organisationId,
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
        $organisationId = $affiliation->organisation?->id;
        if (!$organisationId) {
            return [];
        }

        $custodianIds = CustodianHasProjectUser::query()
            ->with([
                'projectHasUser.affiliation.organisation' => function ($query) use ($organisationId) {
                    $query->where('id', $organisationId);
                }
            ])
            ->whereHas('projectHasUser.affiliation.organisation', function ($query) use ($organisationId) {
                $query->where('id', $organisationId);
            })
            ->pluck('custodian_id')->filter()->unique()->values()->toArray();

        $userIds =  User::whereIn(
            'custodian_user_id',
            CustodianUser::where('custodian_id', $custodianIds)->pluck('id')
        )->pluck('id')->filter()->unique()->values()->toArray();

        return $userIds;
    }
}
