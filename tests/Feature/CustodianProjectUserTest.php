<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use Tests\TestCase;
use App\Models\Custodian;
use App\Models\ProjectHasUser;
use App\Models\CustodianHasProjectUser;
use App\Models\Project;
use App\Models\State;
use Tests\Traits\Authorisation;

class CustodianHasProjectUserControllerTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/custodian_approvals';

    protected Custodian $custodian;
    protected Project $project;
    protected ProjectHasUser $projectUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withUsers();

        $this->project = Project::first();
        $this->projectUser = ProjectHasUser::create([
            'project_id' => $this->project->id,
            'user_digital_ident' =>  $this->user->registry->digi_ident,
            'project_role_id' => 1
        ]);

        CustodianHasProjectUser::create([
            'custodian_id' => Custodian::first()->id,
            'project_has_user_id' => $this->projectUser->id,
        ]);
    }

    public function test_index_returns_all_project_users_for_custodian(): void
    {
        $response = $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . "/1/projectUsers");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [['id', 'custodian_id', 'project_has_user_id']]
        ]);
    }

    public function test_show_returns_specific_project_user_for_custodian(): void
    {
        $response = $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . "/1/projectUsers/{$this->projectUser->id}");

        $response->assertStatus(200);
    }

    public function test_update_project_user_approval_for_custodian(): void
    {
        $payload = [
            'status' => State::STATE_FORM_RECEIVED,
        ];

        $response = $this->actingAs($this->admin)
            ->json('PUT', self::TEST_URL . "/1/projectUsers/{$this->projectUser->id}", $payload);

        $response->assertStatus(200);
    }
}
