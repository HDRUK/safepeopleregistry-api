<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;

use App\Models\User;
use App\Models\Registry;

use Database\Seeders\HistorySeeder;
use Database\Seeders\IdentitySeeder;
use Database\Seeders\IssuerSeeder;
use Database\Seeders\OrganisationSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\TrainingSeeder;
use Database\Seeders\UserSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;
use Tests\Traits\Authorisation;

class QueryTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/query';

    private $user = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermissionSeeder::class,
            IssuerSeeder::class,
            OrganisationSeeder::class,
            UserSeeder::class,
            IdentitySeeder::class,
            HistorySeeder::class,
            TrainingSeeder::class,

        ]);

        $this->user = User::where('id', 1)->first();
    }

    public function test_the_application_can_query_the_system(): void
    {
        $registry = Registry::where('id', 1)->first();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                'ident' => $registry->digi_ident,
            ]
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertNotNull($content['user']);
        $this->assertNotNull($content['user']['identity']);
        $this->assertEquals(
            $content['user']['identity']['registry_id'],
            $content['user']['registry_id']
        );
        $this->assertNotNull($content['registry']);
        $this->assertNotNull($content['registry']['training']);
        $this->assertNotNull($content['registry']['history']);
    }
}
