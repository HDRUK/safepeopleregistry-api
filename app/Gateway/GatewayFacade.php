<?php

namespace App\Gateway;

use Illuminate\Support\Facades\Facade;

class GatewayFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'gateway';
    }
}
