<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;

class ProjectPolicy
{
    public function viewProjectUserDetails(User $user, Project $project): bool
    {
        $projectCustodianUsers = $project->custodianUsers($project->id);

        $projectCustodianUserIds = $projectCustodianUsers['custodians']->pluck('custodianUsers')->flatten()->pluck('user.id')->toArray();

        return in_array($user->id, $projectCustodianUserIds);
    }
}
