<?php

namespace App\Observers;

use Keycloak;
use App\Models\User;
use App\Models\Registry;

class RegistryObserver
{
    public function created(Registry $registry)
    {
        $user = User::where('registry_id', $registry->id)->first();
        dd($user);
        if ($user !== null) {
            Keycloak::updateSoursdDigitalIdentifier($user);
        }
    }
}
