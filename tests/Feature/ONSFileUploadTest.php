<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Database\Seeders\CustodianSeeder;
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
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/ons_researcher_feed';

    private $user = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserSeeder::class,
            PermissionSeeder::class,
            CustodianSeeder::class,
            OrganisationSeeder::class,
        ]);

        $this->user = User::where('id', 1)->first();
    }

    public function test_the_application_can_receive_ons_feed(): void
    {
        $file = UploadedFile::fake()->create('../test_files/ons_test_26062024.csv');

        $this->headers[] = [
            'Content-Type' => 'multipart/form-data',
        ];

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'file' => $file,
                ]
            );

        $response->assertStatus(200);

        $content = $response->decodeResponseJson()['data'];
        $this->assertTrue($content);
    }
}
