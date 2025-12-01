<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Affiliations\Traits\AffiliationNotification;

class ExpiresCertifications extends Notification
{
    use Queueable;
    use AffiliationNotification;

    private $user;
    private $training;
    private $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $training, $type)
    {
        $this->user = $user;
        $this->training = $training;
        $this->type = $type;
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
                'new' => $this->training,
                'time' => now(),
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        $url = config('speedi.system.portal_url') . '/en/organisation/profile/user-administration/employees-and-students/' . $this->user->id . '/affiliations';

        switch ($this->type) {
            case 'user':
                return "Your training certificate for course {$this->training->training_name} has expired.";

            case 'organisation':
                return "Person {$this->user->first_name} {$this->user->last_name} training certificate for course {$this->training->training_name} has expired. [<a href=\"{$url}\">Go to User profile</a>]";

            case 'custodian':
                return "Person {$this->user->first_name} {$this->user->last_name} training certificate for course {$this->training->training_name} has expired.";

            default:
                break;
        }
    }

}
