<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class SendGrid extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'sendgrid';
    }
}
