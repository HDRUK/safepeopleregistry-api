<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailSendFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $jobUuid;
    public $errorMessage;
    public $errorClass;

    /**
     * Create a new event instance.
     */
    public function __construct(string $jobUuid, string $errorMessage)
    {
        $this->jobUuid = $jobUuid;
        $this->errorMessage = $errorMessage;
    }
}
