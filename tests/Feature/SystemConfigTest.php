<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\Authorisation;

class SystemConfigTest extends TestCase
{
    use Authorisation;

    public const TEST_URL = '/api/v1/system_config';

    public function setUp(): void
    {
        parent::setUp();
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

    public function test_the_application_cannot_get_config_by_name(): void
    {
        $systemConfigName = fake()->words(3, true);

        $response = $this->json(
            'GET',
            self::TEST_URL . "/{$systemConfigName}",
            []
        );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }
}
