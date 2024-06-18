<?php

namespace App\OrcID;

use Illuminate\Support\Facades\Facade;

class OrcIDFacade extends Facade {
    protected static function getFacadeAccessor(): string
    {
        return 'orcid';
    }
}