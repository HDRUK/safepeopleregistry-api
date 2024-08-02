<?php

namespace Tests\Feature;

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

    public const TEST_URL = '/api/v1/query';

    private $headers = [];

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

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer '.$this->getAuthToken(),
        ];
    }

    public function test_the_application_can_query_the_system(): void
    {
        $registry = Registry::where('id', 1)->first();

        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'ident' => $registry->digi_ident,
            ],
            $this->headers
        );

        $response->assertStatus(200);
        // ->assertJsonStructure([
        //     'data' => [
        //         'user',
        //         'identity',
        //         'history',
        //         'training',
        //         // 'projects',
        //         'organisations',
        //     ]
        // ]);
    }
}
