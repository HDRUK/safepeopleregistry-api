<?php

namespace Tests\Feature;

use Carbon\Carbon;

use Tests\TestCase;

use App\Models\Registry;

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Traits\Authorisation;

class RegistryTest extends TestCase
{
    use RefreshDatabase, Authorisation;

    const TEST_URL = '/api/v1/registries';

    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer ' . $this->getAuthToken(),
        ];
    }

    public function test_the_application_can_list_registries(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL,
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHaskey('data', $response);
    }

    public function test_the_application_can_show_registries(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'user_id' => 1,
                'dl_ident' => '23897592835298352',
                'pp_ident' => 'PASSPORTIDENT 92387429874 A',
                'verified' => false,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $response = $this->json(
            'GET',
            self::TEST_URL . '/' . $content,
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_create_registries(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'user_id' => 1,
                'dl_ident' => '23897592835298352',
                'pp_ident' => 'PASSPORTIDENT 92387429874 A',
                'verified' => false,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_registries(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'user_id' => 1,
                'dl_ident' => '23897592835298352',
                'pp_ident' => 'PASSPORTIDENT 92387429874 A',
                'verified' => false,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $newDate = Carbon::now()->subYears(2);

        $response = $this->json(
            'PUT',
            self::TEST_URL . '/' . $content,
            [
                'user_id' => 1,
                'dl_ident' => '23897592835298352',
                'pp_ident' => 'PASSPORTIDENT 92387429874 A',
                'verified' => true,
            ],
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['verified'], true);
    }

    public function test_the_application_can_delete_registries(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'user_id' => 1,
                'dl_ident' => '23897592835298352',
                'pp_ident' => 'PASSPORTIDENT 92387429874 A',
                'verified' => false,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $response = $this->json(
            'DELETE',
            self::TEST_URL . '/' . $content,
            $this->headers
        );

        $response->assertStatus(200);
    }
}