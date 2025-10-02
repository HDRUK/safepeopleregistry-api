<?php

namespace App\Notifications\ProjectHasUser;

use App\Models\Affiliation;
use App\Models\User;
use App\Models\Custodian;
use App\Models\Organisation;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\ProjectHasUser\Traits\ProjectHasUserNotification;

class ProjectHasUserCreatedEntityUser extends Notification
{
    use Queueable;
    use ProjectHasUserNotification;

    public function __construct(Project $project, Organisation $organisation, Affiliation $affiliation, User $user)
    {
        $message = "A custodian added you and Organisation " . $organisation->organisation_name . " to " . $project->title;

        $this->buildNotification($message, $affiliation);

        $this->sendEmail($affiliation, $affiliation->email, $user->id, [
            '[[user.email]]' => $affiliation->email,
            '[[organisation.name]]' => $organisation->organisation_name,
            '[[project.title]]' => $project->title,
            '[[project.id]]' => $project->id,
        ]);
    }
}
