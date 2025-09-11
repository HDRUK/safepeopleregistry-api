<?php

namespace App\Observers;

use App\Models\File;
use App\Jobs\SendEmailJob;
use App\Models\Organisation;
use Hdruk\LaravelMjml\Models\EmailTemplate;
use App\Jobs\ProcessCSVSubmission;
use App\Models\OrganisationHasFile;
use App\Models\User;

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

        if (strtolower($file->type) === File::FILE_TYPE_DECLARATION_SRO &&
            strtolower($file->status) === File::FILE_STATUS_PROCESSED) {
            $organisationId = OrganisationHasFile::where([
                'file_id' => $file->id,
            ])->first()->organisation_id;

            $this->sendEmail($file, $organisationId);
        }
    }

    protected function sendEmail(File $file, int $organisationId): void
    {
        $organisation = Organisation::where('id', $organisationId)->first();
        $template = EmailTemplate::where('identifier', 'sro_application_file')->first();
        $user = User::where([
            'organisation_id' => $organisationId,
            'user_group' => 'ORGANISATIONS'
        ])->first();

        $newRecipients = [
            'id' => $user->id,
            'email' => config('speedi.system.support_email'),
        ];

        $replacements = [
            '[[organisation.organisation_name]]' => $organisation->organisation_name,
            '[[file.name]]' => $file->name,
            '[[env(REGISTRY_IMAGE_URL)]]' => config('speedi.system.registry_image_url'),
        ];

        SendEmailJob::dispatch($newRecipients, $template, $replacements, $newRecipients['email']);
    }
}
