<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustodianAddSponsorToProject extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $project;
    protected $organisation;
    protected $for;

    public function __construct($user, $project, $organisation, $for)
    {
        $this->user = $user;
        $this->project = $project;
        $this->organisation = $organisation;
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
            'causer_action' => 'sponsorship_added',
            'message' => $this->generateMessage(),
            'details' => [
                'custodian_name' => $this->user->first_name . ' ' . $this->user->last_name,
                'organisation' => $this->organisation->organisation_name,
                'project_id' => $this->project->id,
                'project_name' => $this->project->title,
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        switch ($this->for) {
            case 'user':
                return "Data Custodian {$this->user->first_name} {$this->user->last_name} has requested sponsorship from Organisation {$this->organisation->organisation_name} for your Project {$this->project->title}";

            case 'organisation':
                return "Data Custodian {$this->user->first_name} {$this->user->last_name} has requested Sponsorship from your Organisation {$this->organisation->organisation_name} for your Project {$this->project->title}.";

            case 'current_custodian':
                return "You requested Sponsorship from Organisation {$this->organisation->organisation_name} for Project {$this->project->title}.";

            case 'custodian':
                return "Data Custodian {$this->user->first_name} {$this->user->last_name} requested Sponsorship from Organisation {$this->organisation->organisation_name} for Project {$this->project->title}.";

            default:
                break;
        }
    }
}
