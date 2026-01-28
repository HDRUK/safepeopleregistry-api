<?php

namespace App\Notifications\ProjectHasUser;

use App\Models\User;
use App\Models\Project;
use App\Models\Affiliation;
use App\Models\Organisation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\ProjectHasUser\Traits\ProjectHasUserNotification;

class ProjectHasUserCreatedEntityUser extends Notification implements ShouldQueue
{
    use Queueable;
    use ProjectHasUserNotification;

    public function __construct(Project $project, Organisation $organisation, Affiliation $affiliation, User $user)
    {
        $message = "A custodian added you and Organisation " . $organisation->organisation_name . " to " . $project->title;

        $this->buildNotification($message, $affiliation);

        $this->sendEmail($affiliation, $affiliation->email, $user->id, [
            '[[user.email]]' => $affiliation->email ?? $user->email,
            '[[organisation.name]]' => $organisation->organisation_name,
            '[[project.title]]' => $project->title,
            '[[project.id]]' => $project->id,
        ]);
    }
}
