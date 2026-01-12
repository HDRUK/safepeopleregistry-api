<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Affiliations\Traits\AffiliationNotification;

class ExpiresTrainings extends Notification implements ShouldQueue
{
    use Queueable;
    use AffiliationNotification;

    private $user;
    private $training;
    private $for;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $training, $for)
    {
        $this->user = $user;
        $this->training = $training;
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
            'action_url' => $this->getUrl(),
            'details' => [
                'new' => $this->training,
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        $url = config('speedi.system.portal_url') . '/en/organisation/profile/user-administration/employees-and-students/' . $this->user->id . '/affiliations';

        switch ($this->for) {
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

    public function getUrl()
    {
        $url = config('speedi.system.portal_url') . '/en/organisation/profile/user-administration/employees-and-students/' . $this->user->id . '/affiliations';
        if ($this->for === 'organisation') {
            return $url;
        }

        return null;
    }

}
