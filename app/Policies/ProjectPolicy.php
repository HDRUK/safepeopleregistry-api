<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;

class ProjectPolicy
{
    public function viewProjectUserDetails(User $user, Project $project): bool
    {
        $projectCustodianUserIds = $project->custodianUserIds($project->id)->toArray();

        return in_array($user->id, $projectCustodianUserIds);
    }
}
