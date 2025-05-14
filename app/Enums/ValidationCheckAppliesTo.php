<?php

namespace App\Enums;

use App\Models\Organisation;
use App\Models\ProjectHasUser;

enum ValidationCheckAppliesTo: string
{
    case ProjectUser = ProjectHasUser::class;
    case Organisation = Organisation::class;

    public static function values(): array
    {
        return [
            self::ProjectUser->value,
            self::Organisation->value,
        ];
    }
}
