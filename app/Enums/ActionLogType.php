<?php

namespace App\Enums;

enum ActionLogType: string
{
    case USER = \App\Models\User::class;
    case ORGANISATION = \App\Models\Organisation::class;
    case CUSTODIAN =  \App\Models\Custodian::class;

    public static function values(): array
    {
        return [
            self::USER->value,
            self::ORGANISATION->value,
            self::CUSTODIAN->value,
        ];
    }
}
