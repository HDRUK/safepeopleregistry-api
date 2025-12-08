<?php

namespace App\Traits\Notifications;

use App\Models\User;
use App\Models\Project;
use App\Models\CustodianUser;
use App\Models\ProjectHasUser;
use App\Models\CustodianHasProjectUser;
use App\Notifications\CustodianAddApprover;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CustodianRemoveApprover;
use App\Models\CustodianHasProjectOrganisation;
use App\Notifications\CustodianUserStatusUpdate;
use App\Notifications\CustodianProjectStateUpdate;
use App\Notifications\CustodianProjectDetailsUpdate;
use App\Notifications\CustodianOrganisationStatusUpdate;

trait NotificationCustodianManager
{
    // custodian change project state
    // send notification to users & organisations & custodians
    public function notifyOnProjectStateChange($loggedInUserId, $projectId, $oldStatus, $newStatus)
    {
        $loggedInUser = User::where('id', $loggedInUserId)->first();
        $project = Project::where('id', $projectId)->first();

        if (is_null($loggedInUser) || is_null($loggedInUser->custodian_user_id) || is_null($project)) {
            return;
        }

        $custodianId = $loggedInUser->custodian_user_id;

        // users
        $userIds = CustodianHasProjectUser::query()
            ->where('custodian_id', $custodianId)
            ->with([
                'projectHasUser.registry.user:id,registry_id,first_name,last_name,email',
                'projectHasUser.project',
            ])
            ->whereHas('projectHasUser.project', function ($query) use ($project) {
                $query->where('id', $project->id);
            })
            ->get()->pluck('projectHasUser.registry.user.id')->toArray();
        if ($userIds) {
            $users = User::query()
                ->where('user_group', User::GROUP_ORGANISATIONS)
                ->whereIn('organisation_id', $userIds)
                ->get();
            foreach ($users as $user) {
                Notification::send($user, new CustodianProjectStateUpdate($loggedInUser, $project, $oldStatus, $newStatus, 'organisation'));
            }
        }

        // organisation
        $organisationIds = CustodianHasProjectOrganisation::query()
            ->where('custodian_id', $custodianId)
            ->with([
                'projectOrganisation',
            ])
            ->whereHas('projectOrganisation.project', function ($query) use ($project) {
                $query->where('id', $project->id);
            })
            ->get()->pluck('projectOrganisation.organisation_id')->toArray();
        if ($organisationIds) {
            $userOrganisations = User::query()
                ->where('user_group', User::GROUP_ORGANISATIONS)
                ->whereIn('organisation_id', $organisationIds)
                ->get();

            foreach ($userOrganisations as $userOrganisation) {
                Notification::send($userOrganisation, new CustodianProjectStateUpdate($loggedInUser, $project, $oldStatus, $newStatus, 'organisation'));
            }
        }

        // custodian
        $userCustodians = User::whereIn(
            'custodian_user_id',
            CustodianUser::where('custodian_id', $custodianId)
                ->pluck('id')
        )->get();

        foreach ($userCustodians as $userCustodian) {
            if ((int)$userCustodian->id === (int)$loggedInUserId) {
                Notification::send($userCustodian, new CustodianProjectStateUpdate($loggedInUser, $project, $oldStatus, $newStatus, 'current_custodian'));
            } else {
                Notification::send($userCustodian, new CustodianProjectStateUpdate($loggedInUser, $project, $oldStatus, $newStatus, 'custodian'));
            }
        }

    }

    // custodian changes validation status of user
    // send notification to users & organisations & custodians
    public function notifyOnUserStateChange(int $loggedInUserId, int $custodianId, int $projectUserId, ?string $newState, ?string $oldState)
    {
        $projectUser = ProjectHasUser::with([
            'project',
            'registry.user',
            'affiliation',
            'affiliation.organisation'
        ])->findOrFail($projectUserId);

        $project = $projectUser->project;
        $user = $projectUser->registry->user;
        $affiliation = $projectUser->affiliation;
        $organisation = $affiliation?->organisation;
        $userOrganisation = User::where('organisation_id', $affiliation?->organisation_id)->first();
        $userCurr = User::where('id', $loggedInUserId)->first();
        $userCustodians = User::whereIn(
            'custodian_user_id',
            CustodianUser::where('custodian_id', $custodianId)
                ->pluck('id')
        )->get();

        $details = [
            'custodian_name' => $userCurr->first_name . ' ' . $userCurr->last_name,
            'user_name' => $user->first_name . ' ' . $user->last_name,
            'project_title' => $project->title,
            'organisation_name' => $organisation?->organisation_name,
            'old_state' => $oldState,
            'new_state' => $newState,
        ];

        // user
        Notification::send($user, new CustodianUserStatusUpdate($details, 'user'));

        // organisation
        Notification::send($userOrganisation, new CustodianUserStatusUpdate($details, 'organisation'));

        // custodians
        foreach ($userCustodians as $userCustodian) {
            if ((int)$userCustodian->id === (int)$loggedInUserId) {
                Notification::send($userCustodian, new CustodianUserStatusUpdate($details, 'current_custodian'));
            } else {
                Notification::send($userCustodian, new CustodianUserStatusUpdate($details, 'custodian'));
            }
        }
    }

    // custodian changes validation status of organisation
    // send notification to users & organisations & custodians
    public function notifyOnOrganisationStateChange(int $loggedInUserId, int $custodianId, int $projectOrganisationId, ?string $newState, ?string $oldState)
    {
        $loggedInUser = User::where('id', $loggedInUserId)->first();

        $custodianHasProjectOrganisation = CustodianHasProjectOrganisation::query()
            ->where([
                'project_has_organisation_id' => $projectOrganisationId,
                'custodian_id' => $custodianId,
            ])
            ->with([
                'projectOrganisation.organisation',
                'projectOrganisation.project'
            ])
            ->first();

        $project = $custodianHasProjectOrganisation->projectOrganisation->project;
        $organisation = $custodianHasProjectOrganisation->projectOrganisation->organisation;

        // user
        $userIds = CustodianHasProjectUser::query()
            ->where('custodian_id', $custodianId)
            ->with([
                'projectHasUser.registry.user:id,registry_id,first_name,last_name,email',
                'projectHasUser.project',
            ])
            ->whereHas('projectHasUser.project', function ($query) use ($project) {
                $query->where('id', $project->id);
            })
            ->get()->pluck('projectHasUser.registry.user.id')->toArray();

        if ($userIds) {
            $users = User::query()
                ->where('user_group', User::GROUP_ORGANISATIONS)
                ->whereIn('organisation_id', $userIds)
                ->get();
            foreach ($users as $user) {
                Notification::send($user, new CustodianOrganisationStatusUpdate($loggedInUser, $project, $organisation, $oldState, $newState, 'custodian'));
            }
        }

        // organisation
        $userOrgaisations = User::where([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $organisation->id
        ])->get();
        foreach ($userOrgaisations as $userOrgaisation) {
            Notification::send($userOrgaisation, new CustodianOrganisationStatusUpdate($loggedInUser, $project, $organisation, $oldState, $newState, 'custodian'));
        }

        // custodian
        $userCustodians = User::whereIn(
            'custodian_user_id',
            CustodianUser::where('custodian_id', $custodianId)
                ->pluck('id')
        )->get();

        foreach ($userCustodians as $userCustodian) {
            if ((int)$userCustodian->id === (int)$loggedInUserId) {
                Notification::send($userCustodian, new CustodianOrganisationStatusUpdate($loggedInUser, $project, $organisation, $oldState, $newState, 'current_custodian'));
            } else {
                Notification::send($userCustodian, new CustodianOrganisationStatusUpdate($loggedInUser, $project, $organisation, $oldState, $newState, 'custodian'));
            }
        }
    }

    // custodian changes fields in a project
    // send notification to users & organisations & custodians
    public function notifyOnProjectDetailsChange($loggedInUserId, $project, $changes)
    {
        $loggedInUser = User::where('id', $loggedInUserId)->first();

        // user
        $userIds = CustodianHasProjectUser::query()
            ->where('custodian_id', $loggedInUser?->custodian_user_id)
            ->with([
                'projectHasUser.registry.user:id,registry_id,first_name,last_name,email',
                'projectHasUser.project',
            ])
            ->whereHas('projectHasUser.project', function ($query) use ($project) {
                $query->where('id', $project->id);
            })
            ->get()->pluck('projectHasUser.registry.user.id')->toArray();

        if ($userIds) {
            $users = User::query()
                ->where('user_group', User::GROUP_ORGANISATIONS)
                ->whereIn('organisation_id', $userIds)
                ->get();
            foreach ($users as $user) {
                Notification::send($user, new CustodianProjectDetailsUpdate($loggedInUser, $project, $changes, 'user'));
            }
        }

        // organisation
        $organisationIds = CustodianHasProjectOrganisation::query()
            ->where('custodian_id', $loggedInUser?->custodian_user_id)
            ->with([
                'projectOrganisation',
            ])
            ->whereHas('projectOrganisation.project', function ($query) use ($project) {
                $query->where('id', $project->id);
            })
            ->get()->pluck('projectOrganisation.organisation_id')->toArray();

        if ($organisationIds) {
            $userOrganisations = User::query()
                ->where('user_group', User::GROUP_ORGANISATIONS)
                ->whereIn('organisation_id', $organisationIds)
                ->get();

            foreach ($userOrganisations as $userOrganisation) {
                Notification::send($userOrganisation, new CustodianProjectDetailsUpdate($loggedInUser, $project, $changes, 'organisation'));
            }
        }

        // custodian
        $userCustodians = User::whereIn(
            'custodian_user_id',
            CustodianUser::where('custodian_id', $loggedInUser?->custodian_user_id)
                ->pluck('id')
        )->get();

        foreach ($userCustodians as $userCustodian) {
            if ((int)$userCustodian->id === (int)$loggedInUserId) {
                Notification::send($userCustodian, new CustodianProjectDetailsUpdate($loggedInUser, $project, $changes, 'current_custodian'));
            } else {
                Notification::send($userCustodian, new CustodianProjectDetailsUpdate($loggedInUser, $project, $changes, 'custodian'));
            }
        }
    }

    // custodian adds approver
    // send notification to custodians
    public function notifyOnAddedApprover($loggedInUserId, $approver)
    {
        $loggedInUser = User::where('id', $loggedInUserId)->first();

        $userCustodians = User::whereIn(
            'custodian_user_id',
            CustodianUser::where('custodian_id', $loggedInUser?->custodian_user_id)
                ->pluck('id')
        )->get();

        foreach ($userCustodians as $userCustodian) {
            if ((int)$userCustodian->id === (int)$loggedInUserId) {
                Notification::send($userCustodian, new CustodianAddApprover($loggedInUser, $approver, 'current_custodian'));
            } else {
                Notification::send($userCustodian, new CustodianAddApprover($loggedInUser, $approver, 'custodian'));
            }
        }
    }

    // Custodian remove approver
    // send notification to custodians
    public function notifyOnRemovedApprover($loggedInUserId, $approver)
    {
        $loggedInUser = User::where('id', $loggedInUserId)->first();

        $userCustodians = User::whereIn(
            'custodian_user_id',
            CustodianUser::where('custodian_id', $loggedInUser?->custodian_user_id)
                ->whereNot('id', $approver->id)
                ->pluck('id')
        )->get();

        \Log::info('notifyOnRemovedApprover', [
            'userCustodians' => $userCustodians,
            'approver' => $approver,
        ]);

        foreach ($userCustodians as $userCustodian) {
            if ((int)$userCustodian->id === (int)$loggedInUserId) {
                Notification::send($userCustodian, new CustodianRemoveApprover($loggedInUser, $approver, 'current_custodian'));
            } else {
                Notification::send($userCustodian, new CustodianRemoveApprover($loggedInUser, $approver, 'custodian'));
            }
        }
    }
}
