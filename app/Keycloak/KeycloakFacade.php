<?php

namespace App\Keycloak;

use Illuminate\Support\Facades\Facade;

class KeycloakFacade extends Facade {
    protected static function getFacadeAccessor(): string
    {
        return 'keycloak';
    }
}
