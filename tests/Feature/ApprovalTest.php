<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\Models\User;
use App\Models\Issuer;
use App\Models\Organisation;
use App\Models\UserHasIssuerApproval;
use App\Models\OrganisationHasIssuerApproval;

use Database\Seeders\UserSeeder;
use Database\Seeders\IssuerSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\OrganisationSeeder;

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Traits\Authorisation;

class ApprovalTest extends TestCase
{
    use RefreshDatabase, Authorisation;

    const TEST_URL = '/api/v1/approvals';

    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermissionSeeder::class,
            IssuerSeeder::class,
            OrganisationSeeder::class,
            UserSeeder::class,
        ]);

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer ' . $this->getAuthToken(),
        ];
    }

    public function test_the_application_can_approve_users(): void
    {
        $user = User::where('id', 1)->first();
        $issuer = Issuer::where('id', 1)->first();

        $response = $this->json(
            'POST',
            self::TEST_URL . '/researcher',
            [
                'user_id' => $user->id,
                'issuer_id' => $issuer->id,
            ],
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertTrue($response->decodeResponseJson()['data']);

        $test = UserHasIssuerApproval::where([
            'user_id' => $user->id,
            'issuer_id' => $issuer->id,
        ])->first();

        $this->assertTrue($test !== null);
    }

    public function test_the_application_can_approve_organisations(): void
    {
        $organisation = Organisation::where('id', 1)->first();
        $issuer = Issuer::where('id', 1)->first();

        $response = $this->json(
            'POST',
            self::TEST_URL . '/organisation',
            [
                'organisation_id' => $organisation->id,
                'issuer_id' => $issuer->id,
            ],
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertTrue($response->decodeResponseJson()['data']);

        $test = OrganisationHasIssuerApproval::where([
            'organisation_id' => $organisation->id,
            'issuer_id' => $issuer->id,
        ])->first();

        $this->assertTrue($test !== null);
    }

    public function test_the_application_can_delete_organisation_approvals(): void
    {
        $organisation = Organisation::where('id', 1)->first();
        $issuer = Issuer::where('id', 1)->first();

        $response = $this->json(
            'POST',
            self::TEST_URL . '/organisation',
            [
                'organisation_id' => $organisation->id,
                'issuer_id' => $issuer->id,
            ],
            $this->headers
        );

        $response->assertStatus(200);

        $response = $this->json(
            'DELETE',
            self::TEST_URL . '/organisation/1/issuer/1',
            $this->headers
        );

        $response->assertStatus(200);
    }

    public function test_the_application_can_delete_user_approvals(): void
    {
        $user = User::where('id', 1)->first();
        $issuer = Issuer::where('id', 1)->first();

        $response = $this->json(
            'POST',
            self::TEST_URL . '/researcher',
            [
                'user_id' => $user->id,
                'issuer_id' => $issuer->id,
            ],
            $this->headers
        );

        $response->assertStatus(200);

        $response = $this->json(
            'DELETE',
            self::TEST_URL . '/researcher/1/issuer/1',
            $this->headers
        );

        $response->assertStatus(200);
    }
}