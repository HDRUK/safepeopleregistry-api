<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\Issuer;
use App\Models\Organisation;
use App\Models\OrganisationHasIssuerApproval;
use App\Models\User;
use App\Models\UserHasIssuerApproval;
use Database\Seeders\IssuerSeeder;
use Database\Seeders\OrganisationSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class ApprovalTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/approvals';

    private $user = null;
    private $registry = null;
    private $issuer = null;
    private $organisation = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermissionSeeder::class,
            IssuerSeeder::class,
            OrganisationSeeder::class,
            UserSeeder::class,
        ]);

        $this->user = User::where('id', 1)->first();

        $this->issuer = Issuer::where('id', 1)->first();
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
                    'issuer_id' => $this->issuer->id,
                ],
            );

        $response->assertStatus(200);
        $this->assertTrue($response->decodeResponseJson()['data']);

        $test = UserHasIssuerApproval::where([
            'user_id' => $this->user->id,
            'issuer_id' => $this->issuer->id,
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
                    'issuer_id' => $this->issuer->id,
                ],
            );

        $response->assertStatus(200);
        $this->assertTrue($response->decodeResponseJson()['data']);

        $test = OrganisationHasIssuerApproval::where([
            'organisation_id' => $this->organisation->id,
            'issuer_id' => $this->issuer->id,
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
                    'issuer_id' => $this->issuer->id,
                ],
            );

        $response->assertStatus(200);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . '/organisation/1/issuer/1',
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
                    'issuer_id' => $this->issuer->id,
                ],
            );

        $response->assertStatus(200);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . '/researcher/1/issuer/1',
            );

        $response->assertStatus(200);
    }
}
