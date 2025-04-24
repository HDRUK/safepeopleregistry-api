<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use App\Traits\CommonFunctions;
use Illuminate\Support\Str;

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

        $this->user = User::where('user_group', User::GROUP_USERS)->first();
        $this->custodian_admin = User::where('user_group', User::GROUP_CUSTODIANS)->first();
        $this->custodian_admin->update([
            'keycloak_id'=> (string) Str::uuid(),
            'unclaimed' => 0,
        ]);
        $this->organisation_admin = User::where('user_group', User::GROUP_ORGANISATIONS)->where("is_delegate",0)->first();
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
            $endpoint = substr($r,1);
            $payload =  $this->getMockedKeycloakPayload();
            switch ($endpoint) {
                case 'custodians':
                    $payload['sub'] = $this->custodian_admin->keycloak_id;
                    break;
                case 'organisations':
                    $payload['sub'] = $this->organisation_admin->keycloak_id;
                    break;
                default:
                    $payload['sub'] = $this->user->keycloak_id;
                    break;
            }
            $response = $this->actingAsKeycloakUser($this->user, $payload)
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
