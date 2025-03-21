<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\Custodian;
use App\Models\Organisation;
use App\Models\OrganisationHasCustodianApproval;
use App\Models\User;
use App\Models\UserHasCustodianApproval;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class ApprovalTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/approvals';

    private $user = null;
    private $registry = null;
    private $custodian = null;
    private $organisation = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('user_group', 'USERS')->first();

        $this->custodian = Custodian::where('id', 1)->first();
        $this->organisation = Organisation::where('id', 1)->first();
    }

    public function test_the_application_can_approve_users(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/researcher',
                [
                    'user_id' => $this->user->id,
                    'custodian_id' => $this->custodian->id,
                ],
            );

        $response->assertStatus(200);
        $this->assertTrue($response->decodeResponseJson()['data']);

        $test = UserHasCustodianApproval::where([
            'user_id' => $this->user->id,
            'custodian_id' => $this->custodian->id,
        ])->first();

        $this->assertTrue($test !== null);
    }

    public function test_the_application_can_approve_organisations(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/organisation',
                [
                    'organisation_id' => $this->organisation->id,
                    'custodian_id' => $this->custodian->id,
                ],
            );

        $response->assertStatus(200);
        $this->assertTrue($response->decodeResponseJson()['data']);

        $test = OrganisationHasCustodianApproval::where([
            'organisation_id' => $this->organisation->id,
            'custodian_id' => $this->custodian->id,
        ])->first();

        $this->assertTrue($test !== null);
    }

    public function test_the_application_can_delete_organisation_approvals(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/organisation',
                [
                    'organisation_id' => $this->organisation->id,
                    'custodian_id' => $this->custodian->id,
                ],
            );

        $response->assertStatus(200);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . '/organisation/1/custodian/1',
            );

        $response->assertStatus(200);
    }

    public function test_the_application_can_delete_user_approvals(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/researcher',
                [
                    'user_id' => $this->user->id,
                    'custodian_id' => $this->custodian->id,
                ],
            );

        $response->assertStatus(200);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . '/researcher/1/custodian/1',
            );

        $response->assertStatus(200);
    }

    public function test_the_application_can_get_user_custodian_approvals(): void
    {
        UserHasCustodianApproval::truncate();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/researcher',
                [
                    'user_id' => $this->user->id,
                    'custodian_id' => $this->custodian->id,
                ],
            );

        $response->assertStatus(200);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/user/'. $this->user->id . '/custodian/' . $this->custodian->id,
            );

        $response->assertStatus(200);
        $responseData = $response->decodeResponseJson()['data'];
        $this->assertEquals(
            [
                [
                    'user_id' => $this->user->id,
                    'custodian_id' => $this->custodian->id,
                ]
            ],
            $responseData
        );

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/user/'. $this->user->id . '/custodian/' . 100,
        );

        $response->assertStatus(404);

    }

    public function test_the_application_can_get_organisation_custodian_approvals(): void
    {
        $orgIsApproved = OrganisationHasCustodianApproval::where(
            [
                'custodian_id' => $this->custodian->id,
                'organisation_id' => $this->organisation->id
            ]
        )->exists();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/organisation/'. $this->organisation->id . '/custodian/' . $this->custodian->id,
            );
        $response->assertStatus($orgIsApproved ? 200 : 404);

    }
}
