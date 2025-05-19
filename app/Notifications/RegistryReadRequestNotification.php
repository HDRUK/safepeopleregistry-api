<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RegistryReadRequestNotification extends Notification
{
    use Queueable;

    private $message;
    private $details;
    private $buttonUrls;

    public function __construct($readRequest, $custodianName)
    {
        $this->message = $custodianName . ' requested SOURSD API access to view your data on ' . $readRequest->created_at->toFormattedDayDateString() . '.';
        $this->details = 'This request is likely because you have previously opted out of public registry inclusion.';
        $this->details .= '<ul>';
        $this->details .= '<li>You can either approve or deny the request from ' . $custodianName . ' below. The Data Custodian will be notified of your decision.</li>';
        $this->details .= '<li>SOURSD API access allows the custodian to respond to webhooks automatically and validate you within their own systems.</li>';
        $this->details .= '<li>No data will be shared with the custodian via the SOURSD API, unless you approve their request.</li>';
        $this->details .= '</ul>';

        $this->buttonUrls = [
            'Approve' => $readRequest->id,
            'Deny' => $readRequest->id,
        ];
    }

    public function via($notifiable)
    {
        return [
            'database',
        ];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
            'details' => $this->details,
            'buttonUrls' => $this->buttonUrls,
            'time' => Carbon::now(),
        ];
    }
}
