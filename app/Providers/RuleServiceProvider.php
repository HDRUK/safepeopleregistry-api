<?php

namespace App\Providers;

use App\Rules\IdentityVerificationRule;
use App\Rules\UKDataProtectionRule;
use Illuminate\Support\ServiceProvider;

class RuleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('rules.identityVerification', IdentityVerificationRule::class);
        $this->app->bind('rules.ukDataProtectionRule', UKDataProtectionRule::class);
    }
}
