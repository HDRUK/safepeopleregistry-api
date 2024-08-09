<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Registry;
use App\Models\InfringementHasResolution;

use Database\Seeders\UserSeeder;
use Database\Seeders\InfringementSeeder;
use Database\Seeders\ResolutionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

use Tests\Traits\Authorisation;

class ResolutionTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;

    public const TEST_URL = '/api/v1/resolutions';

    private $user = null;
    private $registry = null;
    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserSeeder::class,
            InfringementSeeder::class,
            ResolutionSeeder::class,
        ]);

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer '.$this->getAuthToken(),
        ];

        $this->user = User::where('id', 1)->first();
        $this->registry = Registry::where('id', $this->user->registry_id)->first();

    }

    public function test_the_application_can_list_resolutions_by_registry_id(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL . '/' . $this->registry->id,
            $this->headers
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
        $response = $this->json(
            'POST',
            self::TEST_URL . '/' . $this->registry->id,
            $this->prepareResolutionPayload(),
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertEquals($response->decodeResponseJson()['message'], 'success');
    }

    public function test_the_application_can_create_resolutions_with_infringement_id_by_registry_id(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL . '/' . $this->registry->id,
            $this->prepareResolutionPayloadWithInfringementId(),
            $this->headers
        );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson();

        $this->assertEquals($content['message'], 'success');

        $infringement = InfringementHasResolution::where([
            'infringement_id' => 1,
            'resolution_id' => $content['data'],
        ])->get();

        $this->assertTrue(count($infringement) === 1);
    }

    private function prepareResolutionPayload(): array
    {
        return [
            'comment' => fake()->sentence(5),
            'issuer_by' => 1,
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
