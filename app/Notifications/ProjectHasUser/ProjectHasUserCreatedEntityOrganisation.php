<?php

namespace App\Notifications\ProjectHasUser;

use App\Models\Affiliation;
use App\Models\User;
use App\Models\Project;
use App\Models\Custodian;
use App\Models\Organisation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\ProjectHasUser\Traits\ProjectHasUserNotification;

class ProjectHasUserCreatedEntityOrganisation extends Notification
{
    use Queueable;
    use ProjectHasUserNotification;

    public function __construct(Custodian $custodian, Project $project, Affiliation $affiliation, User $user)
    {
        $message = $custodian->name . " added your Organisation and User " . $affiliation->email . " to " . $project->title;

        $this->buildNotification($message, []);

        $organisationId = $affiliation->organisation_id;
        $organisation = Organisation::where('id', $organisationId)->first();

        $this->sendEmail($affiliation, $user, [
            '[[custodian.name]]' => $custodian->name,
            '[[user.email]]' => $affiliation->email,
            '[[organisation.name]]' => $organisation->organisation_name,
            '[[project.title]]' => $project->title,
            '[[project.id]]' => $project->id,
        ]);
    }
}
