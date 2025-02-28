<?php

namespace App\Enums;

enum ActionLogType: string
{
    case USER = 'USER';
    case ORGANISATION = 'ORGANISATION';
    case CUSTODIAN = 'CUSTODIAN';

    public static function values(): array
    {
        return [
            self::USER->value,
            self::ORGANISATION->value,
            self::CUSTODIAN->value,
        ];
    }
}
