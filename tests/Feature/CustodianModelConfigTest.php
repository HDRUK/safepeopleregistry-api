<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use App\Traits\CommonFunctions;
use Database\Seeders\UserSeeder;
use Database\Seeders\EntityModelTypeSeeder;
use Database\Seeders\EntityModelSeeder;
use Database\Seeders\CustodianSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RulesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class CustodianModelConfigTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;
    use ActingAsKeycloakUser;
    use CommonFunctions;

    public const TEST_URL = '/api/v1/custodian_config';

    private $user = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermissionSeeder::class,
            UserSeeder::class,
            RulesSeeder::class,
            EntityModelTypeSeeder::class,
            EntityModelSeeder::class,
            CustodianSeeder::class,
        ]);

        $this->user = User::where('id', 1)->first();
    }

    public function test_the_application_can_show_custodian_config_by_custodian_id(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/1'
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
        $this->assertNotNull($response->decodeResponseJson()['data']);
    }

    public function test_the_application_can_create_custodian_config(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'entity_model_id' => 1,
                    'active' => 1,
                    'custodian_id' => 1,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
        $this->assertNotNull($response->decodeResponseJson()['data']);
    }

    public function test_the_application_can_update_custodian_config(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'entity_model_id' => 1,
                    'active' => 1,
                    'custodian_id' => 1,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
        $content = $response->decodeResponseJson();

        $this->assertNotNull($content['data']);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . '/' . $content['data'],
                [
                    'active' => 0,
                ]
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
        $content = $response->decodeResponseJson();
        $this->assertEquals($content['data']['active'], 0);
    }

    public function test_the_application_can_delete_a_custodian_config(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'entity_model_id' => 1,
                    'active' => 1,
                    'custodian_id' => 1,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
        $content = $response->decodeResponseJson();
        $this->assertNotNull($content['data']);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . '/' . $content['data']
            );

        $response->assertStatus(200);
        // $this->assertArrayHasKey('data', $response);
        $content = $response->decodeResponseJson();

        $this->assertNull($content['data']);
        $this->assertEquals($content['message'], 'success');
    }
}
