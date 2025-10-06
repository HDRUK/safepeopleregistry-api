<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Registry;
use Tests\Traits\Authorisation;
use KeycloakGuard\ActingAsKeycloakUser;

class RegistryTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/registries';
    protected $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    public function test_the_application_can_list_registries(): void
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL
            );

        $response->assertStatus(200);
        $this->assertArrayHaskey('data', $response);
    }

    public function test_the_application_can_show_registries(): void
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'dl_ident' => '23897592835298352',
                    'pp_ident' => 'PASSPORTIDENT 92387429874 A',
                    'verified' => false,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . '/' . $content['data']
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_create_registries(): void
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'dl_ident' => '23897592835298352',
                    'pp_ident' => 'PASSPORTIDENT 92387429874 A',
                    'verified' => false,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_registries(): void
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'dl_ident' => '23897592835298352',
                    'pp_ident' => 'PASSPORTIDENT 92387429874 A',
                    'verified' => false,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $newDate = Carbon::now()->subYears(2);

        $response = $this->actingAs($this->admin)
            ->json(
                'PUT',
                self::TEST_URL . '/' . $content['data'],
                [
                    'dl_ident' => '23897592835298352',
                    'pp_ident' => 'PASSPORTIDENT 92387429874 A',
                    'verified' => true,
                ]
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $this->assertEquals($content['data']['verified'], true);
    }

    public function test_the_application_can_delete_registries(): void
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'user_id' => 1,
                    'dl_ident' => '23897592835298352',
                    'pp_ident' => 'PASSPORTIDENT 92387429874 A',
                    'verified' => false,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $response = $this->json(
            'DELETE',
            self::TEST_URL . '/' . $content['data']
        );

        $response->assertStatus(200);
    }

    public function test_the_application_cannot_get_registries(): void
    {
        $latestRegistry = Registry::query()->orderBy('id', 'desc')->first();
        $registryIdTest = $latestRegistry->id + 1;

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "/{$registryIdTest}"
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_cannot_update_registries(): void
    {
        $latestRegistry = Registry::query()->orderBy('id', 'desc')->first();
        $registryIdTest = $latestRegistry->id + 1;

        $response = $this->actingAs($this->admin)
            ->json(
                'PUT',
                self::TEST_URL . "/{$registryIdTest}",
                [
                    'dl_ident' => '23897592835298352',
                    'pp_ident' => 'PASSPORTIDENT 92387429874 A',
                    'verified' => true,
                ]
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_cannot_delete_registries(): void
    {
        $latestRegistry = Registry::query()->orderBy('id', 'desc')->first();
        $registryIdTest = $latestRegistry->id + 1;

        $response = $this->actingAs($this->admin)
            ->json(
                'DELETE',
                self::TEST_URL . "/{$registryIdTest}"
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }
}
