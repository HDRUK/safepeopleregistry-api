<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\Models\Registry;

use Database\Seeders\UserSeeder;
use Database\Seeders\RegistrySeeder;
use Database\Seeders\IdentitySeeder;
use Database\Seeders\HistorySeeder;
use Database\Seeders\TrainingSeeder;
use Database\Seeders\AffiliationSeeder;

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Traits\Authorisation;

class QueryTest extends TestCase
{
    use RefreshDatabase, Authorisation;

    const TEST_URL = '/api/v1/query';

    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            AffiliationSeeder::class,
            UserSeeder::class,
            RegistrySeeder::class,
            IdentitySeeder::class,
            HistorySeeder::class,
            TrainingSeeder::class,
            
        ]);

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer ' . $this->getAuthToken(),
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

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'identity',
                    'history',
                    'training',
                    // 'projects',
                    'affiliations',
                ]
            ]);
    }
}