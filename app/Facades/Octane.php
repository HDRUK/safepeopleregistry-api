<?php
// app/Facades/Octane.php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isRunning()
 */
class Octane extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'octane';
    }
}
