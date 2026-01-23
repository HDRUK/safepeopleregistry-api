<?php

namespace App\Providers;

use App\Models\File;
use App\Models\User;
use App\Models\ONSFile;
use App\Models\Registry;
use App\Models\Custodian;
use App\Models\Affiliation;
use App\Models\Organisation;
use App\Models\CustodianUser;
use App\Models\ProjectHasUser;
use App\Observers\FileObserver;
use App\Observers\UserObserver;
use App\Models\DecisionModelLog;
use App\Models\UserHasDepartments;
use App\Observers\ONSFileObserver;
use App\Models\ProjectHasCustodian;
use App\Models\RegistryHasTraining;
use App\Models\RegistryReadRequest;
use App\Observers\RegistryObserver;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Models\CustodianModelConfig;
use App\Observers\CustodianObserver;
use App\Observers\AuditModelObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use App\Models\ProjectHasOrganisation;
use App\Observers\AffiliationObserver;
use Illuminate\Queue\Events\JobFailed;
use App\Observers\NotificationObserver;
use App\Observers\OrganisationObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\ServiceProvider;
use App\Observers\CustodianUserObserver;
use App\Models\OrganisationHasSubsidiary;
use App\Observers\ProjectHasUserObserver;
use App\Models\CustodianHasValidationCheck;
use App\Observers\DecisionModelLogObserver;
use App\Observers\UserHasDepartmentsObserver;
use App\Observers\ProjectHasCustodianObserver;
use App\Observers\RegistryHasTrainingObserver;
use App\Observers\RegistryReadRequestObserver;
use App\Models\CustodianHasProjectOrganisation;
use App\Observers\CustodianModelConfigObserver;
use App\Observers\ProjectHasOrganisationObserver;
use Illuminate\Notifications\DatabaseNotification;
use App\Observers\OrganisationHasSubsidiaryObserver;
use App\Observers\CustodianHasValidationCheckObserver;
use App\Observers\CustodianHasProjectOrganisationObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Event::listen('eloquent.*', function ($eventName, $payload) {
        //     $model = $payload[0] ?? null;

        //     if ($model instanceof Model) {
        //         App::make(AuditModelObserver::class)->handle($eventName, $model);
        //     }
        // });
    }
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        File::observe(FileObserver::class);
        ONSFile::observe(ONSFileObserver::class);
        Registry::observe(RegistryObserver::class);
        ProjectHasUser::observe(ProjectHasUserObserver::class);
        Custodian::observe(CustodianObserver::class);
        CustodianModelConfig::observe(CustodianModelConfigObserver::class);
        CustodianUser::observe(CustodianUserObserver::class);
        User::observe(UserObserver::class);
        UserHasDepartments::observe(UserHasDepartmentsObserver::class);
        Organisation::observe(OrganisationObserver::class);
        OrganisationHasSubsidiary::observe(OrganisationHasSubsidiaryObserver::class);
        Affiliation::observe(AffiliationObserver::class);
        ProjectHasCustodian::observe(ProjectHasCustodianObserver::class);
        ProjectHasOrganisation::observe(ProjectHasOrganisationObserver::class);
        CustodianHasProjectOrganisation::observe(CustodianHasProjectOrganisationObserver::class);
        CustodianHasValidationCheck::observe(CustodianHasValidationCheckObserver::class);
        RegistryReadRequest::observe(RegistryReadRequestObserver::class);
        RegistryHasTraining::observe(RegistryHasTrainingObserver::class);
        // currently Training but is to be moved to RegistryHasTraining...
        // RegistryHasTraining::observe(RegistryHasTrainingObserver::class);
        DatabaseNotification::observe(NotificationObserver::class);
        DecisionModelLog::observe(DecisionModelLogObserver::class);

        Event::listen(MessageSent::class, function (MessageSent $event) {
            $messageId = $event->sent?->getMessageId();
        
            // Extract custom headers you added
            $headers = $event->message->getHeaders();
            $modelId = $headers->get('X-Model-ID')?->getBodyAsString();
            $threadId = $headers->get('X-Thread-Id')?->getBodyAsString();
            
            // Try to get response/debug info
            $debug = $event->sent?->getDebug();
            $response = $event->sent?->getOriginalMessage()?->toString();
            
            // Get SMTP response if available (for debugging)
            $envelope = $event->sent?->getEnvelope();
            
            // Log SendGrid response
            \Log::info('AppServiceProvider', [
                'message_id' => $messageId,
                'model_id' => $modelId,
                'thread_id' => $threadId,
                // 'to' => array_keys($event->message->getTo()),
                // 'subject' => $event->message->getSubject(),
                // 'debug' => $debug,
                // 'response' => $response,
                // 'envelope' => $envelope ? [
                //     'sender' => $envelope->getSender()?->getAddress(),
                //     'recipients' => array_map(fn($r) => $r->getAddress(), $envelope->getRecipients())
                // ] : null,
            ]);
        });

        Queue::failing(function (JobFailed $event) {
            $job = $event->job;
            $uuid = $job ->uuid();

            $connectionName = $event->connectionName;
            $queueName      = method_exists($job, 'getQueue') ? $job->getQueue() : null;
            $jobName        = method_exists($job, 'resolveName') ? $job->resolveName() : null; // class name usually
            $jobId          = method_exists($job, 'getJobId') ? $job->getJobId() : null;
            $attempts       = method_exists($job, 'attempts') ? $job->attempts() : null;
            $e = $event->exception;

            // Log structured info (better than dumping huge strings)
            Log::error('Queue job failed', [
                'connection' => $connectionName,
                'queue'      => $queueName,
                'job_name'   => $jobName,
                'job_id'     => $jobId,
                'job_uuid'   => $uuid,
                'attempts'   => $attempts,
                'exception'  => get_class($e),
                'message'    => $e->getMessage(),
            ]);
        });

    }
}
