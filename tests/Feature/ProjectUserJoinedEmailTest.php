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

    public function test_every_custodian_user_is_emailed_when_a_user_joins_a_project(): void
    {
        $this->withUsers(withCustodianUser: true);
        Notification::fake();
        $custodianId = $this->custodian_admin->custodian_user->custodian_id;
        [$readOnly] = $this->makeCustodianUserActor($custodianId, CustodianRole::ReadOnly->value);

        $registry = Registry::factory()->create();
        User::factory()->create(['user_group' => User::GROUP_USERS, 'registry_id' => $registry->id, 'unclaimed' => 0]);
        $affiliation = Affiliation::factory()->create([
            'organisation_id' => Organisation::factory()->create()->id,
            'registry_id' => $registry->id,
            'from' => Carbon::today()->subYear()->toDateString(),
        ]);
        $project = Project::factory()->create();
        $project->custodians()->attach($custodianId);

        // The email is sent by ProjectHasUserObserver; every observer runs once they're on, and some call Keycloak
        Http::fake();
        $this->enableObservers();
        // BaseDemoSeeder leaves 'seeding' set in whichever test seeds the database, and the observer skips
        // notifications while it's set
        app()->instance('seeding', false);

        ProjectHasUser::create([
            'project_id' => $project->id,
            'user_digital_ident' => $registry->digi_ident,
            'project_role_id' => ProjectRole::first()->id,
            'affiliation_id' => $affiliation->id,
        ]);

        // The existing in-app notification, sent from the same loop
        Notification::assertSentTo($readOnly, ProjectHasUserCreatedEntityCustodian::class);

        $this->assertJoinedEmailSentTo($this->custodian_admin);
        $this->assertJoinedEmailSentTo($readOnly);
    }

    private function assertJoinedEmailSentTo(User $recipient): void
    {
        // The job's template is private. EmailTemplate's public $identifier property hides the stored value,
        // so read it with getAttribute()
        Queue::assertPushed(SendEmailJob::class, fn (SendEmailJob $job) => $job->to['id'] === $recipient->id
            && (fn () => $this->template?->getAttribute('identifier'))->call($job) === 'project_user_added_custodian');
    }
}
