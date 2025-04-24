<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use App\Models\Registry;
use App\Models\Infringement;
use App\Models\InfringementHasResolution;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class ResolutionTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/resolutions';
    
    private $registry = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
        $this->registry = Registry::where('id', $this->user->registry_id)->first();

    }

    public function test_the_application_can_list_resolutions_by_registry_id(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/' . $this->registry->id
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertArrayHaskey('data', $response);

        foreach ($content as $resolution) {
            $this->assertTrue($resolution['registry_id'] === 1);
        }
    }

    public function test_the_application_can_create_resolutions_by_registry_id(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'POST',
            self::TEST_URL . '/' . $this->registry->id,
            $this->prepareResolutionPayload()
        );

        $response->assertStatus(201);
        $this->assertEquals($response->decodeResponseJson()['message'], 'success');
    }

    public function test_the_application_can_create_resolutions_with_infringement_id_by_registry_id(): void
    {
        $inf = Infringement::create([
            'reported_by' => 1,
            'comment' => fake()->sentence(5),
            'raised_against' => $this->registry->id,
        ]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'POST',
            self::TEST_URL . '/' . $this->registry->id,
            $this->prepareResolutionPayloadWithInfringementId()
        );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson();

        $this->assertEquals($content['message'], 'success');

        $infringement = InfringementHasResolution::where([
            'infringement_id' => $inf->id,
            'resolution_id' => $content['data'],
        ])->count();

        $this->assertTrue($infringement === 1);
    }

    private function prepareResolutionPayload(): array
    {
        return [
            'comment' => fake()->sentence(5),
            'custodian_by' => 1,
            'resolved' => fake()->randomElement([0, 1]),
        ];
    }

    private function prepareResolutionPayloadWithInfringementId(): array
    {
        $arr = $this->prepareResolutionPayload();
        $arr['infringement_id'] = 1;

        return $arr;
    }
}
