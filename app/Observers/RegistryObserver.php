<?php

namespace App\Observers;

use Keycloak;
use App\Models\User;
use App\Models\Registry;

class RegistryObserver
{
    public function created(Registry $registry)
    {
        // $user = User::where('registry_id', $registry->id)->first();
        // if ($user !== null || !in_array(env('APP_ENV'), ['testing', 'ci'])) {
        //     Keycloak::updateSoursdDigitalIdentifier($user);
        // }
    }
}
