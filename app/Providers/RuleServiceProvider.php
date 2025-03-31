<?php

namespace App\Providers;

use App\Rules\Users\IdentityVerificationRule;
use App\Rules\Users\UKDataProtectionRule;
use App\Rules\Users\TrainingRule;
use Illuminate\Support\ServiceProvider;

class RuleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('rules.identityVerification', IdentityVerificationRule::class);
        $this->app->bind('rules.ukDataProtectionRule', UKDataProtectionRule::class);
        $this->app->bind('rules.trainingRule', TrainingRule::class);
    }
}
