<?php

namespace App\Traits\Notifications;

use App\Models\User;
use App\Models\State;
use App\Models\Project;
use App\Models\Organisation;
use App\Models\CustodianUser;
use App\Models\ProjectHasCustodian;
use App\Models\CustodianHasProjectUser;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrganisationSponsorProjectApproved;
use App\Notifications\OrganisationSponsorProjectRejected;

trait NotificationOrganisationManager
{
    public function notifyOnProjectSponsorStateChange($loggedInUserId, $organisationId, $projectId, $newState)
    {
        $loggedInUser = User::where('id', $loggedInUserId)->first();
        $organisation = Organisation::where('id', $organisationId)->first();
        $project = Project::where('id', $projectId)->first();

        if ($newState === 'approved') {
            // user
            $users = $this->getUsersByProjectId($projectId);
            if ($users) {
                Notification::send($users, new OrganisationSponsorProjectApproved($loggedInUser, $organisation, $project, 'user'));
            }

            // organisation
            Notification::send($loggedInUser, new OrganisationSponsorProjectApproved($loggedInUser, $organisation, $project, 'organisation'));

            // custodian
            $userCustodians = $this->getCustodainsByOrgIdAndProjectId($organisationId, $projectId);
            Notification::send($userCustodians, new OrganisationSponsorProjectApproved($loggedInUser, $organisation, $project, 'custodian'));
        }

        if ($newState === 'rejected') {
            // user
            $users = $this->getUsersByProjectId($projectId);
            if ($users) {
                Notification::send($users, new OrganisationSponsorProjectRejected($loggedInUser, $organisation, $project, 'user'));
            }

            // organisation
            $usesOrganisation = $this->getUsersOrganisationSroByOrgId($organisationId);
            Notification::send($loggedInUser, new OrganisationSponsorProjectRejected($loggedInUser, $organisation, $project, 'user'));

            // custodian
            $userCustodians = $this->getCustodainsByOrgIdAndProjectId($organisationId, $projectId);
            Notification::send($userCustodians, new OrganisationSponsorProjectRejected($loggedInUser, $organisation, $project, 'custodian'));
        }
    }

    public function getUsersByProjectId($projectId)
    {
        $userIds = CustodianHasProjectUser::query()
            ->with([
                'projectHasUser.registry.user:id,registry_id,first_name,last_name,email',
                'projectHasUser.project',
            ])
            ->whereHas('projectHasUser.project', function ($query) use ($projectId) {
                $query->where('id', $projectId);
            })
            ->get()->pluck('projectHasUser.registry.user.id')->filter()->unique()->values()->toArray();
        if ($userIds) {
            return $users = User::query()
                ->where('user_group', User::GROUP_ORGANISATIONS)
                ->whereIn('organisation_id', $userIds)
                ->get();
        }

        return null;
    }

    public function getUsersOrganisationSroByOrgId($organisationId)
    {
        return User::where([
            'organisation_id' => $organisationId,
            'is_sro' => 1,
        ])->get();
    }

    public function getCustodainsByOrgIdAndProjectId($organisationId, $projectId)
    {
        $custodianIds = ProjectHasCustodian::where('project_id', 1)->get()->pluck('custodian_id')->toArray();

        if (!count($custodianIds)) {
            return null;
        }

        return User::whereIn(
            'custodian_user_id',
            CustodianUser::where('custodian_id', $custodianIds)->pluck('id')
        )->get();
    }
}