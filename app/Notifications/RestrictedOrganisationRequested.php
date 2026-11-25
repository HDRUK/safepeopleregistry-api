<?php

namespace App\Notifications;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RestrictedOrganisationRequested extends Notification
{
    use Queueable;

    public function __construct(
        protected User $requester,
        protected Organisation $organisation
    ) {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->generateMessage(),
            'details' => [
                'requester_id' => $this->requester->id,
                'organisation_id' => $this->organisation->id,
                'organisation_name' => $this->organisation->organisation_name,
                'email_address' => $this->organisation->lead_applicant_email,
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        return "User " . $this->requester->first_name . " " . $this->requester->last_name
            . " has requested organisation '" . $this->organisation->organisation_name
            . "', which is awaiting approval. They can't use it until it is approved.";
    }
}
