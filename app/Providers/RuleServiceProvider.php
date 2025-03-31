<?php

namespace App\Providers;

use App\Rules\Users\IdentityVerificationRule;
use App\Rules\Users\UKDataProtectionRule;
use App\Rules\Users\TrainingRule;
use App\Rules\Organisations\DataSecurityComplianceRule;
use Illuminate\Support\ServiceProvider;

class RuleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('rules.users.identityVerification', IdentityVerificationRule::class);
        $this->app->bind('rules.users.ukDataProtectionRule', UKDataProtectionRule::class);
        $this->app->bind('rules.users.trainingRule', TrainingRule::class);
        $this->app->bind('rules.organisations.dataSecurityComplianceRule', DataSecurityComplianceRule::class);
    }
}
