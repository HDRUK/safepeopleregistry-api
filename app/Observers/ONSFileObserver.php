<?php

namespace App\Observers;

use App\Jobs\ProcessONSSubmission;
use App\Models\ONSFile;
use App\Models\File;

class ONSFileObserver
{
    public function updated(ONSFile $file): void
    {
        if (strtolower($file->status) === File::FILE_STATUS_PROCESSED) {
            ProcessONSSubmission::dispatch($file);
        }
    }
}
