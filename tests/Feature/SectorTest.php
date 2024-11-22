<?php

namespace Tests\Feature;

use App\Models\Sector;
use Database\Seeders\SectorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class SextorTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;

    public const TEST_URL = '/api/v1/sectors';

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            SectorSeeder::class,
        ]);
    }

    public function test_the_application_can_list_sectors(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL,
            []
        );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()[0]['data'];

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
}
