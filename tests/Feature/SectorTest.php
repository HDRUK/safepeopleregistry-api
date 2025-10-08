<?php

namespace Tests\Feature;

use App\Models\Sector;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class SectorTest extends TestCase
{
    use Authorisation;

    public const TEST_URL = '/api/v1/sectors';

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_the_application_can_list_sectors(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL,
            []
        );

        $response->assertStatus(200);

        $content = $response->decodeResponseJson()['data']['data'];

        $this->assertTrue(count($content) === count(Sector::SECTORS));

        for ($i = 0; $i < count($content); $i++) {
            $this->assertTrue($content[$i]['name'] === Sector::SECTORS[$i]);
        }
    }

    public function test_the_application_can_get_sectors_by_id(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL.'/1',
            []
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];
        $this->assertTrue($content['name'] === 'NHS');
    }

    public function test_the_application_cannot_get_sectors_by_id(): void
    {
        $latestSector = Sector::query()->orderBy('id', 'desc')->first();
        $sectorIdTest = $latestSector ? $latestSector->id + 1 : 1;

        $response = $this->json(
            'GET',
            self::TEST_URL . "/{$sectorIdTest}",
            []
        );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_can_create_sectors(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'name' => fake()->name(),
            ]
        );

        $response->assertStatus(201);
    }

    public function test_the_application_can_update_sectors(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'name' => fake()->name(),
            ]
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
        $sectorId = $response->decodeResponseJson()['data'];

        $response = $this->json(
            'PUT',
            self::TEST_URL . "/{$sectorId}",
            [
                'name' => fake()->name(),
            ]
        );

        $response->assertStatus(200);
    }

    public function test_the_application_cannot_update_sectors_by_id(): void
    {
        $latestSector = Sector::query()->orderBy('id', 'desc')->first();
        $sectorIdTest = $latestSector ? $latestSector->id + 1 : 1;

        $response = $this->json(
            'PUT',
            self::TEST_URL . "/{$sectorIdTest}",
            [
                'name' => fake()->name(),
            ]
        );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_cannot_delete_sectors_by_id(): void
    {
        $latestSector = Sector::query()->orderBy('id', 'desc')->first();
        $sectorIdTest = $latestSector ? $latestSector->id + 1 : 1;

        $response = $this->json(
            'DELETE',
            self::TEST_URL . "/{$sectorIdTest}",
            []
        );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }
}
