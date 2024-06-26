<?php

namespace App\Observers;

use App\Models\ONSFile;

use App\Jobs\ProcessONSSubmission;

class ONSFileObserver
{
    public function updated(ONSFile $file): void
    {
        if ($file->status === 'PROCESSED') {
            ProcessONSSubmission::dispatch($file);
        }
    }
}
