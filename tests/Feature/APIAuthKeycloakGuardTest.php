<?php

namespace Tests\Feature;

use Auth;
use KeycloakGuard\ActingAsKeycloakUser;
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

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
        // $this->liteSetUp();
        // parent class disables this, we renable here to confirm
        // auth does in fact work as intended.
        $this->withMiddleware();

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
    public function test_custodian_admin_can_access_routes(): void
    {
        foreach ($this->getRoutes as $r) {
            if (substr($r, 1) !== 'custodians') {
                continue;
            }

            $response = $this->actingAs($this->custodian_admin)
                             ->json('GET', self::TEST_URL . $r, [
                                 'Accept' => 'application/json',
                             ]);

            $response->assertStatus(200);
        }
    }

    public function test_organisation_admin_can_access_routes(): void
    {
        foreach ($this->getRoutes as $r) {
            if (substr($r, 1) !== 'organisations') {
                continue;
            }

            $response = $this->actingAs($this->organisation_admin)
                             ->json('GET', self::TEST_URL . $r, [
                                 'Accept' => 'application/json',
                             ]);

            $response->assertStatus(200);
        }
    }

    public function test_regular_user_can_access_other_routes(): void
    {
        foreach ($this->getRoutes as $r) {
            $endpoint = substr($r, 1);
            if (in_array($endpoint, ['custodians', 'organisations', 'users'])) {
                continue;
            }

            $response = $this->actingAs($this->user)
                            ->json('GET', self::TEST_URL . $r, [
                                'Accept' => 'application/json',
                            ]);

            $response->assertStatus(200);
        }
    }
}
