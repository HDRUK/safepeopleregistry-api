<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use App\Traits\CommonFunctions;

class APIAuthKeycloakGuardTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;
    use CommonFunctions;

    public const TEST_URL = '/api/v1';

    private $getRoutes = [
        '/users',
        '/training',
        '/custodians',
        '/custodian_users',
        '/departments',
        '/projects',
        '/registries',
        '/experiences',
        '/identities',
        '/organisations',
        '/sectors',
        '/histories',
        '/permissions',
        '/email_templates',
        // '/files',
        // '/request_access',
        '/webhooks/receivers',
        // '/custodian_config',
        '/project_details',
        '/project_roles',
        '/system_config',
    ];

    private $user = null;

    public function setUp(): void
    {
        parent::setUp();
        // $this->liteSetUp();
        // parent class disables this, we renable here to confirm
        // auth does in fact work as intended.
        $this->withMiddleware();

        $this->user = User::where('user_group', 'USERS')->first();
    }

    public function test_the_application_gives_401_for_authed_routes(): void
    {
        foreach ($this->getRoutes as $r) {
            $response = $this->get(self::TEST_URL . $r, [
                'Accept' => 'application/json',
            ]);
            $response->assertStatus(401);
        }
    }

    public function test_the_application_allows_authed_routes(): void
    {
        foreach ($this->getRoutes as $r) {
            $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
                ->json(
                    'GET',
                    self::TEST_URL . $r,
                    [
                        'Accept' => 'application/json',
                    ]
                );

            $response->assertStatus(200);
        }
    }
}
