<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrganisationSponsorProjectApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $organisation;
    protected $project;
    protected $for;

    public function __construct($user, $organisation, $project, $for)
    {
        $this->user = $user;
        $this->organisation = $organisation;
        $this->project = $project;
        $this->for = $for;
    }

    /**
     * Specify the delivery channels.
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Store the notification in the database.
     */
    public function toDatabase($notifiable)
    {
        return [
            'causer_id' => $this->user->id,
            'causer_action' => 'sponsorship_approved',
            'message' => $this->generateMessage(),
            'details' => [
                'organisation' => $this->organisation->organisation_name,
                'project' => $this->organisation->title,
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        switch ($this->for) {
            case 'user':
                return "Organisation {$this->organisation->organisation_name} has confirmed Sponsorship for Project {$this->project->title}";

            case 'organisation':
                return "You confirmed Sponsorship for Project {$this->project->title}.";

            case 'custodian':
                return "Organisation {$this->organisation->organisation_name} approved Sponsorship for Project {$this->project->title}.";

            default:
                break;
        }
    }
}