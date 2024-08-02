<?php

namespace Tests\Feature;

use Database\Seeders\IssuerSeeder;
use Database\Seeders\OrganisationSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class ONSFileUploadTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;

    public const TEST_URL = '/api/v1/ons_researcher_feed';

    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermissionSeeder::class,
            IssuerSeeder::class,
            OrganisationSeeder::class,
        ]);

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer '.$this->getAuthToken(),
        ];
    }

    public function test_the_application_can_receive_ons_feed(): void
    {
        $file = UploadedFile::fake()->create('../test_files/ons_test_26062024.csv');

        $this->headers[] = [
            'Content-Type' => 'multipart/form-data',
        ];

        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'file' => $file,
            ],
            $this->headers
        );

        $response->assertStatus(200);

        $content = $response->decodeResponseJson()['data'];
        $this->assertTrue($content);
    }
}
