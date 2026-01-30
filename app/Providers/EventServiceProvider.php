<?php

namespace App\Providers;

use App\Events\EmailSendFailed;
use App\Listeners\LogEmailSent;
use App\Listeners\LogEmailFailed;
use App\Events\EmailSentSuccessfully;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string|string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            \SocialiteProviders\Keycloak\KeycloakExtendSocialite::class.'@handle',
        ],
         \Illuminate\Queue\Events\JobFailed::class => [
            \App\Listeners\NotifySlackOfFailedJob::class,
        ],
        EmailSentSuccessfully::class => [
            LogEmailSent::class,
        ],
        EmailSendFailed::class => [
            LogEmailFailed::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
