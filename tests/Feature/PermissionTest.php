<?php

namespace Tests\Feature;

use App\Models\Issuer;
use App\Models\Organisation;
use App\Models\OrganisationHasIssuerPermission;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserHasIssuerPermission;
use Database\Seeders\IssuerSeeder;
use Database\Seeders\OrganisationSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class PermissionTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;

    public const TEST_URL = '/api/v1/[[PLACEHOLDER]]/permissions';

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
            'Authorization' => 'bearer '.$this->getAuthToken(),
        ];
    }

    public function test_the_application_can_give_permissions_to_users(): void
    {
        $url = str_replace('[[PLACEHOLDER]]', 'users', self::TEST_URL);

        $user = User::where('id', 1)->first();
        $issuer = Issuer::where('id', 1)->first();

        $permissions = Permission::all();
        $permToAdd = fake()->randomElement($permissions);

        $response = $this->json(
            'POST',
            $url,
            [
                'user_id' => $user->id,
                'issuer_id' => $issuer->id,
                'permissions' => [
                    $permToAdd->id,
                ],
            ],
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertTrue($response->decodeResponseJson()['data']);

        $test = UserHasIssuerPermission::where([
            'user_id' => $user->id,
            'issuer_id' => $issuer->id,
            'permission_id' => $permToAdd->id,
        ])->first();

        $this->assertTrue($test !== null);
    }

    public function test_the_application_can_give_permissions_to_organisations(): void
    {
        $url = str_replace('[[PLACEHOLDER]]', 'organisations', self::TEST_URL);

        $organisation = Organisation::where('id', 1)->first();
        $issuer = Issuer::where('id', 1)->first();

        $permissions = Permission::all();
        $permToAdd = fake()->randomElement($permissions);

        $response = $this->json(
            'POST',
            $url,
            [
                'organisation_id' => $organisation->id,
                'issuer_id' => $issuer->id,
                'permissions' => [
                    $permToAdd->id,
                ],
            ],
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertTrue($response->decodeResponseJson()['data']);

        $test = OrganisationHasIssuerPermission::where([
            'organisation_id' => $organisation->id,
            'issuer_id' => $issuer->id,
            'permission_id' => $permToAdd->id,
        ])->first();

        $this->assertTrue($test !== null);
    }
}
