<?php

namespace App\Observers;

use App\Models\File;
use App\Models\OrganisationHasFile;

use App\Jobs\ProcessCSVSubmission;

class FileObserver
{
    public function updated(File $file): void
    {
        if (strtoupper($file->type) === 'RESEARCHER_LIST' &&
            strtoupper($file->status) === 'PROCESSED') {
                $org = OrganisationHasFile::where([
                    'file_id' => $file->id,
                ])->first()->organisation_id;

                ProcessCSVSubmission::dispatch($file, $org);
            }
    }
}
