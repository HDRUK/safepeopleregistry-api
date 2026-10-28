<?php

namespace App\Rules\Organisations;

use App\Models\File;
use App\Rules\BaseRule;
use Illuminate\Support\Arr;

/**
 * Passes when the Organisation's claimed SRO has an SRO declaration that has been uploaded, and
 * scanned clean, since they were appointed. A change of SRO fails it until a new one is uploaded.
 */
class SroDeclarationCheck extends BaseRule
{
    protected $tag = SroDeclarationCheck::class;

    public function evaluate($model, array $conditions): bool
    {
        if (!$model->claimedSro) {
            return false;
        }

        $appointedAt = $model->sro_appointed_at;

        $path = $conditions['path'] ?? 'files';
        // Not 'expects': the web app appends a string 'expects' to a failed rule's heading.
        $type = $conditions['file_type'] ?? File::FILE_TYPE_DECLARATION_SRO;
        $files = Arr::get($model, $path, null) ?? [];

        // Null when the appointment time is unknown, e.g. the activity log could not account for it,
        // so any declaration counts.
        return collect($files)->contains(
            fn (File $file) => $file->isType($type)
                && strtolower($file->status) === File::FILE_STATUS_PROCESSED
                && ($appointedAt === null || $file->created_at->gte($appointedAt))
        );
    }
}
