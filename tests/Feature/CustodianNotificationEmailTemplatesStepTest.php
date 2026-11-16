<?php

namespace Tests\Feature;

use App\DeploymentSteps\DeploymentStep;
use Database\Seeders\EmailTemplatesSeeder;
use Hdruk\LaravelMjml\Models\EmailTemplate;
use Illuminate\Database\Seeder;
use RuntimeException;
use Tests\TestCase;

/**
 * The deployment step that adds the custodian notification email templates to existing databases.
 */
class CustodianNotificationEmailTemplatesStepTest extends TestCase
{
    private const STEP = 'database/deployment-steps/2026_11_12_120000_reseed_email_templates_custodian_notifications.php';

    private const TEMPLATES = [
        'custodian_invite_read_only',
        'affiliation_ended_custodian_read_only',
        'project_user_added_custodian',
    ];

    public function test_the_step_adds_the_custodian_notification_templates(): void
    {
        EmailTemplate::whereIn('identifier', self::TEMPLATES)->forceDelete();

        $this->step()->handle();

        foreach (self::TEMPLATES as $identifier) {
            $this->assertNotNull(EmailTemplate::where('identifier', $identifier)->first(), "{$identifier} was not added");
        }
    }

    public function test_the_step_fails_when_the_templates_cannot_be_seeded(): void
    {
        // deploy:run only records a step that doesn't throw, so a failure here halts the deploy and the step runs again
        $this->app->instance(EmailTemplatesSeeder::class, new class () extends Seeder {
            public function run(): void
            {
                throw new RuntimeException('Seeding failed');
            }
        });

        $this->expectException(RuntimeException::class);

        $this->step()->handle();
    }

    private function step(): DeploymentStep
    {
        return require base_path(self::STEP);
    }
}
