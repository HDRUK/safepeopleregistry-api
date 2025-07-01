<?php

namespace App\RulesEngineManagementController;

use Illuminate\Support\Facades\Facade;

class RulesEngineManagementControllerFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RulesEngineManagementController::class;
    }
}
