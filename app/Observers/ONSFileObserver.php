<?php

namespace App\Observers;

use App\Jobs\ProcessONSSubmission;
use App\Models\ONSFile;

class ONSFileObserver
{
    public function updated(ONSFile $file): void
    {
        if ($file->status === 'PROCESSED') {
            ProcessONSSubmission::dispatch($file);
        }
    }
}
