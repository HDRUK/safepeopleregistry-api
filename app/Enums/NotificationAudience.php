<?php

namespace App\Enums;

/**
 * Who a notification is written for, so its message can be worded for them.
 */
enum NotificationAudience: string
{
    case User = 'user';
    case Organisation = 'organisation';
    case Custodian = 'custodian';
    // CUSTODIAN_READ_ONLY users: told what happened, but not asked to act
    case CustodianReadOnly = 'custodian_read_only';
}
