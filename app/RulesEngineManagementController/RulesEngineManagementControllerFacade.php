<?php

namespace App\RulesEngineManagementController;

use Illuminate\Support\Facades\Facade;

class RulesEngineManagementController extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'rulesenginemanagementcontroller';
    }
}
