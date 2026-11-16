<?php

namespace Tests\Feature;

use App\Enums\CustodianRole;
use App\Jobs\SendEmailJob;
use App\Models\Affiliation;
use App\Models\Organisation;
use App\Models\Project;
use App\Models\ProjectHasUser;
use App\Models\ProjectRole;
use App\Models\Registry;
use App\Models\User;
use App\Notifications\ProjectHasUser\ProjectHasUserCreatedEntityCustodian;
use Carbon\Carbon;
use Hdruk\LaravelMjml\Models\EmailTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Tests\Traits\CustodianActors;

/**
 * When a user joins a project through an affiliation, every user of the project's Custodians is emailed.
 */
class ProjectUserJoinedEmailTest extends TestCase
{
    use Authorisation;
    use CustodianActors;

    private const TEMPLATE = 'project_user_added_custodian';

    private User $readOnly;

    private Registry $registry;

    private Affiliation $affiliation;

    private Project $project;

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers(withCustodianUser: true);
        Notification::fake();

        $custodianId = $this->custodian_admin->custodian_user->custodian_id;
        [$this->readOnly] = $this->makeCustodianUserActor($custodianId, CustodianRole::ReadOnly->value);

        $this->registry = Registry::factory()->create();
        User::factory()->create(['user_group' => User::GROUP_USERS, 'registry_id' => $this->registry->id, 'unclaimed' => 0]);
        $this->affiliation = Affiliation::factory()->create([
            'organisation_id' => Organisation::factory()->create()->id,
            'registry_id' => $this->registry->id,
            'from' => Carbon::today()->subYear()->toDateString(),
        ]);
        $this->project = Project::factory()->create();
        $this->project->custodians()->attach($custodianId);
    }

    public function test_every_custodian_user_is_emailed_when_a_user_joins_a_project(): void
    {
        $this->joinProject();

        // The existing in-app notification, sent from the same loop
        Notification::assertSentTo($this->readOnly, ProjectHasUserCreatedEntityCustodian::class);

        $this->assertJoinedEmailSentTo($this->custodian_admin);
        $this->assertJoinedEmailSentTo($this->readOnly);
    }

    public function test_a_user_still_joins_a_project_when_the_email_template_is_missing(): void
    {
        EmailTemplate::where('identifier', self::TEMPLATE)->forceDelete();

        $projectHasUser = $this->joinProject();

        $this->assertDatabaseHas('project_has_users', ['id' => $projectHasUser->id]);
        // Custodian users are still told in the app
        Notification::assertSentTo($this->readOnly, ProjectHasUserCreatedEntityCustodian::class);
        Queue::assertNotPushed(SendEmailJob::class, fn (SendEmailJob $job) => $this->templateOf($job) === self::TEMPLATE);
    }

    private function joinProject(): ProjectHasUser
    {
        // The email is sent by ProjectHasUserObserver; every observer runs once they're on, and some call Keycloak
        Http::fake();
        $this->enableObservers();
        // BaseDemoSeeder leaves 'seeding' set in whichever test seeds the database, and the observer skips
        // notifications while it's set
        app()->instance('seeding', false);

        return ProjectHasUser::create([
            'project_id' => $this->project->id,
            'user_digital_ident' => $this->registry->digi_ident,
            'project_role_id' => ProjectRole::first()->id,
            'affiliation_id' => $this->affiliation->id,
        ]);
    }

    private function assertJoinedEmailSentTo(User $recipient): void
    {
        Queue::assertPushed(SendEmailJob::class, fn (SendEmailJob $job) => $job->to['id'] === $recipient->id
            && $this->templateOf($job) === self::TEMPLATE);
    }

    private function templateOf(SendEmailJob $job): ?string
    {
        // The job's template is private. EmailTemplate's public $identifier property hides the stored value,
        // so read it with getAttribute()
        return (fn () => $this->template?->getAttribute('identifier'))->call($job);
    }
}
