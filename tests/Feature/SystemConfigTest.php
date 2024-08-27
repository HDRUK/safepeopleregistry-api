<?php

namespace Tests\Feature;

use Database\Seeders\SystemConfigSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;
use Tests\Traits\Authorisation;

class SystemConfigTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;

    public const TEST_URL = '/api/v1/system_config';

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            SystemConfigSeeder::class,
        ]);
    }

    public function test_the_application_can_list_config(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL,
            []
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'][0];
        $this->assertEquals($content['name'], 'PER_PAGE');
        $this->assertEquals($content['value'], 25);
    }

    public function test_the_application_can_get_config_by_name(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL.'/PER_PAGE',
            []
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];
        $this->assertEquals($content['name'], 'PER_PAGE');
        $this->assertEquals($content['value'], 25);
    }
}
