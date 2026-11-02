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
}
