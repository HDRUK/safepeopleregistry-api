<?php

namespace Tests\Feature;

use App\Models\Accreditation;
use KeycloakGuard\ActingAsKeycloakUser;
use Carbon\Carbon;
use App\Models\Registry;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class AccreditationTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/accreditations';

    private $registry = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();

        $this->registry = Registry::where('id', $this->user->registry_id)->first();

        $this->company1 = fake()->company();
        $this->id1 = fake()->uuid();
        $this->company2 = fake()->company();
        $this->id2 = fake()->uuid();
    }

    public function test_the_application_can_list_accreditations_by_registry_id(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . "/{$this->registry->id}",
                $this->prepareAccreditationPayload()
            );

        $response->assertStatus(201);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "/{$this->registry->id}"
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertArrayHaskey('data', $response);
        $this->assertTrue(count($content['data']) === 1);
        $this->assertEquals($content['data'][0]['associated_organisation_name'], $this->company1);
    }

    public function test_the_application_cannot_list_accreditations_by_registry_id(): void
    {
        $latestRegistry = Registry::query()->orderBy('id', 'desc')->first();
        $registryIdTest = $latestRegistry ? $latestRegistry->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "/{$registryIdTest}"
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_can_create_accreditations_by_registry_id(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . "/{$this->registry->id}",
                $this->prepareAccreditationPayload()
            );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson();

        $this->assertEquals($content['message'], 'success');
        $this->assertNotNull($content['data']);
    }

    public function test_the_application_cannot_create_accreditations_by_registry_id(): void
    {
        $latestRegistry = Registry::query()->orderBy('id', 'desc')->first();
        $registryIdTest = $latestRegistry ? $latestRegistry->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . "/{$registryIdTest}",
                $this->prepareAccreditationPayload()
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_can_update_accreditations_by_registry_id(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'POST',
            self::TEST_URL . "/{$this->registry->id}",
            $this->prepareAccreditationPayload()
        );

        $content = $response->decodeResponseJson();

        $response->assertStatus(201);
        $this->assertEquals($content['message'], 'success');

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . "/{$content['data']}/registries/{$this->registry->id}",
                $this->prepareUpdatedAccreditationPayload()
            );

        $content = $response->decodeResponseJson();

        $response->assertStatus(200);

        $this->assertEquals($content['data']['associated_organisation_name'], $this->company2);
        $this->assertEquals($content['data']['id_string'], $this->id2);
    }

    public function test_the_application_cannot_update_accreditations_by_registry_id(): void
    {
        $latestRegistry = Registry::query()->orderBy('id', 'desc')->first();
        $registryIdTest = $latestRegistry ? $latestRegistry->id + 1 : 1;

        $latestAccreditation = Accreditation::query()->orderBy('id', 'desc')->first();
        $accreditationIdTest = $latestAccreditation ? $latestAccreditation->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . "/{$accreditationIdTest}/registries/{$registryIdTest}",
                $this->prepareUpdatedAccreditationPayload()
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_can_delete_accreditations_by_registry_id(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'POST',
            self::TEST_URL . "/{$this->registry->id}",
            $this->prepareAccreditationPayload()
        );

        $content = $response->decodeResponseJson();

        $response->assertStatus(201);
        $this->assertEquals($content['message'], 'success');

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . "/{$content['data']}/registries/{$this->registry->id}",
                $this->prepareEditedAccreditationPayload()
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();
        $this->assertEquals($content['message'], 'success');
    }

    public function test_the_application_cannot_delete_accreditations_by_registry_id(): void
    {
        $latestRegistry = Registry::query()->orderBy('id', 'desc')->first();
        $registryIdTest = $latestRegistry ? $latestRegistry->id + 1 : 1;

        $latestAccreditation = Accreditation::query()->orderBy('id', 'desc')->first();
        $accreditationIdTest = $latestAccreditation ? $latestAccreditation->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . "/{$accreditationIdTest}/registries/{$registryIdTest}",
                $this->prepareEditedAccreditationPayload()
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    private function prepareAccreditationPayload(): array
    {
        $awardedDate = Carbon::parse(fake()->date());

        return [
            'associated_organisation_name' => $this->company1,
            'id_string' => $this->id1,
            'issue_date' => $awardedDate->toDateString(),
            'expiry_date' => $awardedDate->addYear(2)->toDateString(),
        ];
    }

    private function prepareUpdatedAccreditationPayload(): array
    {
        $awardedDate = Carbon::parse(fake()->date());

        return [
            'associated_organisation_name' => $this->company2,
            'id_string' => $this->id2,
            'issue_date' => $awardedDate->toDateString(),
            'expiry_date' => $awardedDate->addYear(4)->toDateString(),
        ];
    }

    private function prepareEditedAccreditationPayload(): array
    {
        return [
            'associated_organisation_name' => $this->company1,
        ];
    }
}
