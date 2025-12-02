<?php

namespace App\Notifications\Organisations;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrganisationUpdateProfile extends Notification implements ShouldQueue
{
    use Queueable;

    private $message;
    private $newOrganisation;
    private $oldOrganisation;
    private $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $newOrganisation, $oldOrganisation, $type)
    {
        $this->newOrganisation = $newOrganisation;
        $this->oldOrganisation = $oldOrganisation;
        $this->type = $type;
        $this->message = $this->generateMessage($user);
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
            'message' => $this->message,
            'details' => [
                'old' => $this->oldOrganisation,
                'new' => $this->newOrganisation,
                'time' => now(),
            ],
            'time' => now(),
        ];
    }

    public function generateMessage($user)
    {
        switch ($this->type) {
            case 'organisation':
                return "{$user->first_name} {$user->last_name} changed organisation profile.";

            case 'custodian':
                return "Organisation {$this->newOrganisation->organisation_name} has changed their profile.";

            default:
                break;
        }
    }

}


// class OrganisationUpdateProfileDetails extends Notification
// {
//     use Queueable;

//     private $message;
//     private $user;
//     private $newOrganisation;
//     private $oldOrganisation;
//     private $type;

//     /**
//      * Create a new notification instance.
//      */
//     public function __construct($user, $newOrganisation, $oldOrganisation, $type)
//     {
//         $this->newOrganisation = $newOrganisation;
//         $this->oldOrganisation = $oldOrganisation;
//         $this->type = $type;
//         $this->message = $this->generateMessage($user);
//     }

//     /**
//      * Specify the delivery channels.
//      */
//     public function via($notifiable)
//     {
//         return ['database'];
//     }

//     /**
//      * Store the notification in the database.
//      */
//     public function toDatabase($notifiable)
//     {
//         return [
//             'message' => $this->message,
//             'details' => [
//                 'old' => $this->oldOrganisation,
//                 'new' => $this->newOrganisation,
//                 'time' => now(),
//             ],
//             'time' => now(),
//         ];
//     }

//     public function generateMessage($user)
//     {
//         switch ($this->type) {
//             case 'organisation':
//                 return "{$user->first_name} {$user->last_name} changed organisation profile.";

//             case 'custodian':
//                 return "Organisation {$this->newOrganisation->organisation_name} has changed their profile.";

//             default:
//                 break;
//         }
//     }

// }
