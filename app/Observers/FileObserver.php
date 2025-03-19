<?php

namespace App\Observers;

use App\Jobs\ProcessCSVSubmission;
use App\Models\File;
use App\Models\OrganisationHasFile;

class FileObserver
{
    public function updated(File $file): void
    {
        if (strtolower($file->type) === File::FILE_TYPE_RESEARCHER_LIST &&
            strtolower($file->status) === File::FILE_STATUS_PROCESSED) {
            $org = OrganisationHasFile::where([
                'file_id' => $file->id,
            ])->first()->organisation_id;

            ProcessCSVSubmission::dispatch($file, $org);
        }
    }
}
