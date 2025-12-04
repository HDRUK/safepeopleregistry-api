<?php

namespace App\Notifications\Organisations;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExpiresSecurityCertifications extends Notification implements ShouldQueue
{
    use Queueable;

    private $type;
    private $code;
    private $organisation;
    private $for;

    public function __construct($type, $code, $organisation, $for)
    {
        $this->type = $type;
        $this->code = $code;
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
            'message' => $this->generateMessage(),
            'details' => [
                'type' => $this->type,
                'code' => $this->code,
                'time' => now(),
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        switch ($this->for) {
            case 'organisation':
                return "Your certificate for {$this->type} ({$this->code}) has expired";

            case 'custodian':
                return "Organisation {$this->organisation->organisation_name}'s certifcate for {$this->type} ({$this->code}) has expired";

            default:
                break;
        }
    }
}
