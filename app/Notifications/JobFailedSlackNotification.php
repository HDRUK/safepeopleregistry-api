<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Queue\Events\JobFailed;

class JobFailedSlackNotification extends Notification
{
    public function __construct(protected JobFailed $event) {}

    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        $job = $this->event->job;
        $env = strtoupper(config('app.env'));


        return (new SlackMessage)
            ->error()
            ->content("🚨 Safe People Registry Queue Job Failed ({$env}) 🚨")
            ->attachment(function ($attachment) use ($job, $env) {
                $attachment->fields([
                    'Environment' => $env,
                    'Job'        => get_class($job),
                    'Queue'      => $job->getQueue(),
                    'Connection' => $this->event->connectionName,
                    'Exception'  => $this->event->exception->getMessage(),
                ]);
            });
    }

}


