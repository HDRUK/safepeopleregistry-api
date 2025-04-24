<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use App\Traits\CommonFunctions;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class CustodianModelConfigTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;
    use CommonFunctions;

    public const TEST_URL = '/api/v1/custodian_config';

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
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
        $this->enableObservers();

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

    public function test_the_application_can_get_entity_models_for_specific_custodian(): void
    {
        $custodianId = 1;
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                "/api/v1/custodian_config/{$custodianId}/entity_models?entity_model_type=decision_models"
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
        $content = $response->decodeResponseJson();
        $this->assertNotNull($content['data']);
        $this->assertIsArray($content['data']);

        if (count($content['data']) > 0) {
            $this->assertArrayHasKey('id', $content['data'][0]);
            $this->assertArrayHasKey('name', $content['data'][0]);
            $this->assertArrayHasKey('description', $content['data'][0]);
            $this->assertArrayHasKey('active', $content['data'][0]);
        }
    }

    public function test_the_application_returns_error_for_invalid_entity_model_type(): void
    {
        $custodianId = 1;
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                "/api/v1/custodian_config/{$custodianId}/entity_models?entity_model_type=invalid_type"
            );

        $response->assertStatus(400);
        $content = $response->decodeResponseJson();
        $this->assertEquals('Invalid argument(s)', $content['message']);
        $this->assertEquals('The selected entity model type is invalid.', $content['errors'][0]['message']);
    }

    public function test_the_application_requires_entity_model_type_parameter(): void
    {
        $custodianId = 1;
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                "/api/v1/custodian_config/{$custodianId}/entity_models"
            );

        $response->assertStatus(400);
        $content = $response->decodeResponseJson();
        $this->assertEquals('Invalid argument(s)', $content['message']);
        $this->assertEquals('The entity model type field is required.', $content['errors'][0]['message']);
    }

    public function test_the_application_returns_not_found_for_invalid_custodian_id(): void
    {
        $invalidCustodianId = 9999;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                "/api/v1/custodian_config/{$invalidCustodianId}/entity_models?entity_model_type=decision_models"
            );

        $response->assertStatus(404);
        $content = $response->decodeResponseJson();
        $this->assertNull($content['data']);
    }
}
