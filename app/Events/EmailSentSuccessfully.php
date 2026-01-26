<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailSentSuccessfully
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $jobUuid;
    public $messageId;
    public $recipient;

    /**
     * Create a new event instance.
     */
    public function __construct(string $jobUuid, ?string $messageId)
    {
        $this->jobUuid = $jobUuid;
        $this->messageId = $messageId;
    }
}
