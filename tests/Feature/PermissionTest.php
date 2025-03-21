<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\Custodian;
use App\Models\Organisation;
use App\Models\OrganisationHasCustodianPermission;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserHasCustodianPermission;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class PermissionTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/[[PLACEHOLDER]]/permissions';

    private $user = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('user_group', 'USERS')->first();
    }

    public function test_the_application_can_give_permissions_to_users(): void
    {
        $url = str_replace('[[PLACEHOLDER]]', 'users', self::TEST_URL);

        $custodian = Custodian::where('id', 1)->first();

        $permissions = Permission::all();
        $permToAdd = fake()->randomElement($permissions);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                $url,
                [
                'user_id' => $this->user->id,
                'custodian_id' => $custodian->id,
                'permissions' => [
                    $permToAdd->id,
                ],
            ]
            );

        $response->assertStatus(200);
        $this->assertTrue($response->decodeResponseJson()['data']);

        $test = UserHasCustodianPermission::where([
            'user_id' => $this->user->id,
            'custodian_id' => $custodian->id,
            'permission_id' => $permToAdd->id,
        ])->first();

        $this->assertTrue($test !== null);
    }

    public function test_the_application_can_give_permissions_to_organisations(): void
    {
        $url = str_replace('[[PLACEHOLDER]]', 'organisations', self::TEST_URL);

        $organisation = Organisation::where('id', 1)->first();
        $custodian = Custodian::where('id', 1)->first();

        $permissions = Permission::all();
        $permToAdd = fake()->randomElement($permissions);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                $url,
                [
                'organisation_id' => $organisation->id,
                'custodian_id' => $custodian->id,
                'permissions' => [
                    $permToAdd->id,
                ],
            ]
            );

        $response->assertStatus(200);
        $this->assertTrue($response->decodeResponseJson()['data']);

        $test = OrganisationHasCustodianPermission::where([
            'organisation_id' => $organisation->id,
            'custodian_id' => $custodian->id,
            'permission_id' => $permToAdd->id,
        ])->first();

        $this->assertTrue($test !== null);
    }
}
