<?php

namespace App\RegistryManagementController;

use Illuminate\Support\Facades\Facade;

class RegistryManagementControllerFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'registrymanagementcontroller';
    }
}
