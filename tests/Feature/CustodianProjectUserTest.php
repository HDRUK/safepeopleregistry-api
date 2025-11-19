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

class CustodianProjectUserTest extends TestCase
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
    }

    public function test_index_cannot_show_all_project_users_for_custodian(): void
    {
        $lastestCustodian = Custodian::query()->orderBy('id', 'desc')->first();
        $custodianIdTest = $lastestCustodian ? $lastestCustodian->id + 1 : 1;

        $response = $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . "/{$custodianIdTest}/projectUsers");

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_show_returns_specific_project_user_for_custodian(): void
    {
        $response = $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . "/1/projectUsers/{$this->projectUser->id}");

        $response->assertStatus(200);
    }

    public function test_cannot_show_returns_specific_project_user_for_custodian(): void
    {
        $lastestCustodian = Custodian::query()->orderBy('id', 'desc')->first();
        $custodianIdTest = $lastestCustodian ? $lastestCustodian->id + 1 : 1;

        $lastestProjectHasUser = ProjectHasUser::query()->orderBy('id', 'desc')->first();
        $projectHasUserIdTest = $lastestProjectHasUser ? $lastestProjectHasUser->id + 1 : 1;

        $response = $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . "/{$custodianIdTest}/projectUsers/{$projectHasUserIdTest}");

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_update_project_user_approval_for_custodian(): void
    {
        $payload = [
            'status' => State::STATE_FORM_RECEIVED,
        ];

        $response = $this->actingAs($this->admin)
            ->json('PUT', self::TEST_URL . "/1/projectUsers/{$this->projectUser->id}", $payload);

        dd($response);

        $response->assertStatus(200);
    }

    public function test_cannot_update_project_user_approval_for_custodian(): void
    {
        $lastestCustodian = Custodian::query()->orderBy('id', 'desc')->first();
        $custodianIdTest = $lastestCustodian ? $lastestCustodian->id + 1 : 1;

        $lastestProjectHasUser = ProjectHasUser::query()->orderBy('id', 'desc')->first();
        $projectHasUserIdTest = $lastestProjectHasUser ? $lastestProjectHasUser->id + 1 : 1;

        $payload = [
            'status' => State::STATE_FORM_RECEIVED,
        ];

        $response = $this->actingAs($this->admin)
            ->json('PUT', self::TEST_URL . "/{$custodianIdTest}/projectUsers/{$projectHasUserIdTest}", $payload);

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }
}
