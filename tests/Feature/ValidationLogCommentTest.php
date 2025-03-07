<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use App\Models\Custodian;
use App\Models\Registry;
use App\Models\ProjectHasUser;
use App\Models\Project;
use App\Models\ProjectHasCustodian;
use App\Models\ValidationLog;
use App\Models\ValidationLogComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class ValidationLogCommentTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/validation_log_comments';

    protected User $user;

    private function add_user_and_custodian_to_project()
    {
        ProjectHasUser::create([
            'project_id' => $this->project->id,
            'user_digital_ident' => $this->user->registry->digi_ident,
        ]);
        ProjectHasCustodian::create([
            'project_id' => $this->project->id,
            'custodian_id' => $this->custodian->id ,
        ]);

    }

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->registry = Registry::factory()->create();
        $this->user->update(['registry_id' => $this->registry->id]);
        $this->custodian = Custodian::factory()->create();
        $this->project = Project::factory()->create();
        $this->add_user_and_custodian_to_project();
    }



    public function test_it_returns_a_validation_log_comment_via_api()
    {

        $comment = ValidationLogComment::create([
            'comment' => 'test comment',
            'user_id' => $this->user->id,
            'validation_log_id' => ValidationLog::first()->id
        ]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/' . $comment->id,
        );

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $comment->id,
                'validation_log_id' => $comment->validation_log_id,
                'user_id' => $comment->user_id,
                'comment' => $comment->comment,
            ]
        ]);

    }

    public function test_it_cannot_find_a_validation_log_comment_via_api()
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/1'
        );

        $response->assertStatus(404);

    }

    public function test_it_can_create_a_validation_log_comment_via_api()
    {

        $payload = [
             'comment' => 'test comment',
             'user_id' => $this->user->id,
             'validation_log_id' => ValidationLog::first()->id
         ];

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'POST',
            self::TEST_URL,
            $payload
        );

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'validation_log_id' => $payload['validation_log_id'],
                'user_id' => $payload['user_id'],
                'comment' => $payload['comment']
            ]
        ]);

    }

    public function test_it_can_update_a_validation_log_comment_via_api()
    {

        $comment = ValidationLogComment::create([
            'comment' => 'test comment',
            'user_id' => $this->user->id,
            'validation_log_id' => ValidationLog::first()->id
        ]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/' . $comment->id,
        );

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $comment->id,
                'validation_log_id' => $comment->validation_log_id,
                'user_id' => $comment->user_id,
                'comment' => $comment->comment,
            ]
        ]);

        $payload = [
            'comment' => 'updated comment',
        ];

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . '/' . $comment->id,
            $payload
        );
        $response->assertStatus(200);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/' . $comment->id,
        );

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                'id' => $comment->id,
                'validation_log_id' => $comment->validation_log_id,
                'user_id' => $comment->user_id,
                'comment' => $payload['comment'],
            ]
        ]);
    }

    public function test_it_can_delete_a_validation_log_comment_via_api()
    {

        $comment = ValidationLogComment::create([
            'comment' => 'test comment',
            'user_id' => $this->user->id,
            'validation_log_id' => ValidationLog::first()->id
        ]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/' . $comment->id,
        );

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $comment->id,
                'validation_log_id' => $comment->validation_log_id,
                'user_id' => $comment->user_id,
                'comment' => $comment->comment,
            ]
        ]);


        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'DELETE',
            self::TEST_URL . '/' . $comment->id,
        );

        $response->assertStatus(200);
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/' . $comment->id,
        );

        $response->assertStatus(404);



    }





}
