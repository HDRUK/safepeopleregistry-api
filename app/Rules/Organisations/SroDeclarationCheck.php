<?php

namespace App\Rules\Organisations;

use App\Models\File;
use App\Rules\BaseRule;
use Illuminate\Support\Arr;

/**
 * Passes when the Organisation's most recent SRO declaration was uploaded since its claimed SRO was
 * appointed, and scanned clean. A change of SRO fails it until a new one is uploaded.
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

        // Only the most recent declaration is judged, so one that fails its scan fails the rule even
        // if an earlier one passed. Ordered as Organisation::latestSroDeclaration(), from loaded files.
        $latest = collect($files)
            ->filter(fn (File $file) => $file->isType($type))
            ->sortBy([['created_at', 'desc'], ['id', 'desc']])
            ->first();

        if (!$latest || strtolower($latest->status) !== File::FILE_STATUS_PROCESSED) {
            return false;
        }

        // Null when the appointment time is unknown, e.g. the activity log could not account for it,
        // so any declaration counts.
        return $appointedAt === null || $latest->created_at->gte($appointedAt);
    }
}
