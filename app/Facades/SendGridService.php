<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class SendGridService extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'sendgridservice';
    }
}
