<?php

namespace Tests\Feature;

use App\Enums\CustodianRole;
use App\Jobs\SendEmailJob;
use App\Models\Custodian;
use App\Models\CustodianUser;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectHasUser;
use App\Models\Registry;
use App\Models\User;
use App\Models\ValidationCheck;
use App\Models\ValidationLog;
use App\Models\WebhookEventTrigger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use KeycloakGuard\ActingAsKeycloakUser;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Tests\Traits\CustodianActors;

/**
 * The notReadOnly gate guards write routes that have no other authorisation check.
 *
 * Each test sends the same valid request twice: a CUSTODIAN_READ_ONLY user is refused and nothing
 * is written, then a CUSTODIAN_APPROVER in the same Custodian succeeds. The approver half shows the
 * 403 comes from the read-only check, not from validation or another rule. One route per controller.
 */
class CustodianReadOnlyGateTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;
    use CustodianActors;

    private Custodian $custodian;

    private User $readOnly;

    private User $approver;

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers(true);

        $this->custodian = Custodian::findOrFail(
            CustodianUser::findOrFail($this->custodian_admin->custodian_user_id)->custodian_id
        );

        [$this->readOnly] = $this->makeCustodianUserActor($this->custodian->id, CustodianRole::ReadOnly->value);
        [$this->approver] = $this->makeCustodianUserActor($this->custodian->id, CustodianRole::Approver->value);
    }

    public function test_the_gate_only_refuses_read_only_custodian_users(): void
    {
        $this->assertTrue(Gate::forUser($this->readOnly)->denies('notReadOnly'));
        $this->assertTrue(Gate::forUser($this->approver)->allows('notReadOnly'));
        $this->assertTrue(Gate::forUser($this->admin)->allows('notReadOnly'));
        $this->assertTrue(Gate::forUser($this->user)->allows('notReadOnly'));
    }

    public function test_validation_log_comments(): void
    {
        $log = $this->makeCustodianValidationLog();

        $this->assertRefusedThenAllowed('POST', '/api/v1/validation_log_comments', fn (User $actor) => [
            'validation_log_id' => $log->id,
            'user_id' => $actor->id,
            'comment' => 'read-only gate test comment',
        ], 'validation_log_comments', 201);
    }

    public function test_validation_logs(): void
    {
        $log = $this->makeCustodianValidationLog();
        $url = "/api/v1/custodians/{$this->custodian->id}/validation_logs";

        $this->as($this->readOnly)->json('PUT', $url, ['enabled' => 0])->assertStatus(403);
        $this->assertSame(1, (int) ValidationLog::withDisabled()->findOrFail($log->id)->enabled);

        $this->as($this->approver)->json('PUT', $url, ['enabled' => 0])->assertStatus(200);
        $this->assertSame(0, (int) ValidationLog::withDisabled()->findOrFail($log->id)->enabled);
    }

    public function test_validation_checks(): void
    {
        $this->assertRefusedThenAllowed('POST', "/api/v1/custodians/{$this->custodian->id}/validation_checks", fn () => [
            'name' => 'read_only_gate_test_check',
            'description' => 'read-only gate test',
            'applies_to' => ProjectHasUser::class,
            'enabled' => 1,
        ], 'validation_checks', 201);
    }

    public function test_custodian_add_project(): void
    {
        $this->assertRefusedThenAllowed('POST', "/api/v1/custodians/{$this->custodian->id}/projects", fn () => [
            'title' => 'Read-only gate test project',
        ], 'projects', 201);
    }

    public function test_projects(): void
    {
        $this->assertRefusedThenAllowed('POST', '/api/v1/projects', fn () => [
            'title' => 'Read-only gate test project',
        ], 'projects', 201);
    }

    public function test_project_details(): void
    {
        $project = Project::factory()->create();

        $this->assertRefusedThenAllowed('POST', '/api/v1/project_details', fn () => [
            'project_id' => $project->id,
            'datasets' => ['https://doesnot.exist/datasets/1'],
            'other_approval_committees' => ['Committee 1'],
            'data_sensitivity_level' => 'Anonymous',
            'duty_of_confidentiality' => 1,
            'national_data_optout' => 0,
            'request_frequency' => 'ONE-OFF',
            'access_date' => '2025-12-02',
            'access_type' => 1,
            'research_outputs' => ['https://example.com/research-output'],
        ], 'project_details', 201);
    }

    public function test_project_users(): void
    {
        $projectUser = ProjectHasUser::create([
            'project_id' => Project::factory()->create()->id,
            'user_digital_ident' => Registry::factory()->create()->digi_ident,
            'project_role_id' => 1,
        ]);
        $url = "/api/v1/project_users/{$projectUser->id}";

        $this->as($this->readOnly)->json('DELETE', $url)->assertStatus(403);
        $this->assertDatabaseHas('project_has_users', ['id' => $projectUser->id]);

        $this->as($this->approver)->json('DELETE', $url)->assertStatus(200);
        $this->assertDatabaseMissing('project_has_users', ['id' => $projectUser->id]);
    }

    public function test_webhook_receivers(): void
    {
        $trigger = WebhookEventTrigger::factory()->create();

        $this->assertRefusedThenAllowed('POST', '/api/v1/webhooks/receivers', fn () => [
            'custodian_id' => $this->custodian->id,
            'url' => 'https://example.com/read-only-gate-test',
            'webhook_event_id' => $trigger->id,
        ], 'custodian_webhook_receivers', 201);
    }

    public function test_organisation_new_account(): void
    {
        // unclaimed must be the integer 1: anything else takes a branch that calls Keycloak
        $this->assertRefusedThenAllowed('POST', '/api/v1/organisations/new_account', fn () => [
            'organisation_name' => 'Read-only gate test organisation',
            'lead_applicant_email' => 'read-only-gate-test@example.com',
            'unclaimed' => 1,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ], 'organisations', 201);
    }

    public function test_user_permissions(): void
    {
        $this->assertRefusedThenAllowed('POST', '/api/v1/users/permissions', fn () => [
            'user_id' => $this->user->id,
            'custodian_id' => $this->custodian->id,
            'permissions' => [Permission::firstOrFail()->id],
        ], 'user_has_custodian_permissions', 200);
    }

    public function test_trigger_email(): void
    {
        $payload = fn () => [
            'to' => $this->custodian->id,
            'type' => 'custodian',
            'identifier' => 'custodian_invite',
            'unclaimed_user_id' => $this->user->id,
        ];

        $this->assertRefusedThenAllowed('POST', '/api/v1/trigger_email', $payload, 'pending_invites', 200);
    }

    /**
     * Sends the same request as the read-only user (403, nothing written to $table, no email queued)
     * and then as the approver ($successStatus, a row written to $table).
     *
     * @param  \Closure(User): array<string, mixed>  $payload
     */
    private function assertRefusedThenAllowed(
        string $method,
        string $url,
        \Closure $payload,
        string $table,
        int $successStatus
    ): void {
        $rowsBefore = DB::table($table)->count();

        $this->as($this->readOnly)->json($method, $url, $payload($this->readOnly))->assertStatus(403);
        $this->assertSame($rowsBefore, DB::table($table)->count(), "Read-only request wrote to {$table}");
        Queue::assertNotPushed(SendEmailJob::class);

        $this->as($this->approver)->json($method, $url, $payload($this->approver))->assertStatus($successStatus);
        $this->assertGreaterThan($rowsBefore, DB::table($table)->count(), "Approver request didn't write to {$table}");
    }

    private function makeCustodianValidationLog(): ValidationLog
    {
        return ValidationLog::create([
            'entity_type' => Custodian::class,
            'entity_id' => $this->custodian->id,
            'validation_check_id' => ValidationCheck::firstOrFail()->id,
        ]);
    }

    private function as(User $user): self
    {
        return $this->actingAsKeycloakUser($user, $this->getMockedKeycloakPayload())->actingAs($user);
    }
}
