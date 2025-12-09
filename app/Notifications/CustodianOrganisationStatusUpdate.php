<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustodianOrganisationStatusUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $project;
    protected $organisation;
    protected $newState;
    protected $oldState;
    protected $for;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $project, $organisation, $oldState, $newState, $for)
    {
        $this->user = $user;
        $this->project = $project;
        $this->organisation = $organisation;
        $this->newState = convertStates($newState);
        $this->oldState = convertStates($oldState);
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
            'message' => $this->generateMessage(),
            'details' => [
                'new' => $this->newState,
                'old' => $this->oldState,
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        switch ($this->for) {
            case 'user':
                return "Data Custodian {$this->user->first_name} {$this->user->last_name} changed the validation status of Organisation {$this->organisation->organisation_name} from {$this->oldState} to {$this->newState} on project {$this->project->title}";

            case 'organisation':
                return "Data Custodian {$this->user->first_name} {$this->user->last_name} changed the validation status of Organisation {$this->organisation->organisation_name} from {$this->oldState} to {$this->newState} on project {$this->project->title}";

            case 'current_custodian':
                return "You changed the validation status of Organisation {$this->organisation->organisation_name} from {$this->oldState} to {$this->newState} on project {$this->project->title}";

            case 'custodian':
                return "Data Custodian {$this->user->first_name} {$this->user->last_name} changed the validation status of Organisation {$this->organisation->organisation_name} from {$this->oldState} to {$this->newState} on project {$this->project->title}";

            default:
                break;
        }
    }
}
