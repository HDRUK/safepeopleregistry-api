<?php

namespace App\TriggerEmail;

use Illuminate\Support\Facades\Facade;

class TriggerEmailFacade extends Facade {
    protected static function getFacadeAccessor(): string
    {
        return 'triggeremail';
    }
}