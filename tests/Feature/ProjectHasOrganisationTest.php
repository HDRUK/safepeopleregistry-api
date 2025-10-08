<?php

namespace Tests\Feature;

use App\Models\ProjectHasOrganisation;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use KeycloakGuard\ActingAsKeycloakUser;

class ProjectHasOrganisationTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/project_organisations';

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    public function test_the_application_can_list_project_organisations_by_id(): void
    {
        $projectHasOrgasnisationId = 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "/{$projectHasOrgasnisationId}",
            );

        $response->assertStatus(200);
    }

    public function test_the_application_cannot_list_project_organisations_by_id(): void
    {
        $lastestProjectHasOrganisation = ProjectHasOrganisation::query()->orderBy('id', 'desc')->first();
        $projectHasOrgasnisationIdTest = $lastestProjectHasOrganisation ? $lastestProjectHasOrganisation->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "/{$projectHasOrgasnisationIdTest}",
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }
}
