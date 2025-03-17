<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use App\Models\Registry;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class QueryTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/query';

    private $user = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('user_group', 'USERS')->first();
    }

    public function test_the_application_can_query_the_system(): void
    {
        $registry = Registry::where('id', $this->user->registry_id)->first();

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
        // LS - Haven't added history to demo data - removed for now
        //$this->assertNotNull($content['registry']['history']);
    }
}
