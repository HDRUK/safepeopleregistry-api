<?php

namespace App\Listeners;

use App\Models\EmailLog;
use App\Events\EmailSendFailed;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogEmailFailed
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EmailSendFailed $event): void
    {
        Log::info('Email sent successfully', [
            'job_uuid' => $event->jobUuid,
            'timestamp' => now()->toIso8601String(),
        ]);

        $checkEmailLog = EmailLog::where('job_uuid', $event->jobUuid)->first();
        if (is_null($checkEmailLog)) {
            return;
        }

        EmailLog::where('job_uuid', $event->jobUuid)
            ->update([
                'job_status' => 0,
                'error_message' => $event->errorMessage,
            ]);

    }
}
