<?php

namespace App\Notifications\Organisations;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrganisationUserAffiliation extends Notification implements ShouldQueue
{
    use Queueable;

    private $user;
    private $organisation;
    private $status;
    private $for;

    public function __constructor($user, $organisation, $status, $for)
    {
        $this->user = $user;
        $this->organisation = $organisation;
        $this->status = $status;
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
                'user' => $this->user,
                'organisation' => $this->organisation,
                'status' => $this->status,
                'time' => now(),
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        if ($this->status === 'approved') {
            switch ($this->for) {
                case 'user':
                    return "Organisation {$this->organisation->organisation_name} confirmed your affiliation";

                case 'organisation':
                    return "Organisation {$this->organisation->organisation_name} confirmed affiliation with {$this->user->first_name} {$this->user->last_name}";

                case 'custodian':
                    return "Organisation {$this->organisation->organisation_name} confirmed affiliation with {$this->user->first_name} {$this->user->last_name}";

                default:
                    break;
            }
        }

        if ($this->status === 'rejected') {
            switch ($this->for) {
                case 'user':
                    return "Organisation {$this->organisation->organisation_name} declined your affiliation";

                case 'organisation':
                    return "Organisation {$this->organisation->organisation_name} declined affiliation with {$this->user->first_name} {$this->user->last_name}";

                case 'custodian':
                    return "Organisation {$this->organisation->organisation_name} declined affiliation with {$this->user->first_name} {$this->user->last_name}";

                default:
                    break;
            }
        }

    }
}