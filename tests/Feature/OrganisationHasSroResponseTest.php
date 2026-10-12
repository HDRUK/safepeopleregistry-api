<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use KeycloakGuard\ActingAsKeycloakUser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class OrganisationHasSroResponseTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/organisations';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    public static function sroStates(): array
    {
        return [
            'no sro' => [null, false],
            'unclaimed sro' => [1, false],
            'claimed sro' => [0, true],
        ];
    }

    #[DataProvider('sroStates')]
    public function test_show_reports_whether_the_organisation_has_an_sro(?int $sroUnclaimed, bool $expected): void
    {
        $organisation = Organisation::factory()->create();

        if ($sroUnclaimed !== null) {
            User::factory()->create([
                'user_group' => User::GROUP_ORGANISATIONS,
                'organisation_id' => $organisation->id,
                'is_sro' => 1,
                'unclaimed' => $sroUnclaimed,
            ]);
        }

        $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . "/{$organisation->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.has_sro', $expected);
    }

    public function test_index_does_not_report_has_sro(): void
    {
        $organisation = Organisation::factory()->create();

        $organisations = $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . '?per_page=1000')
            ->assertStatus(200)
            ->json('data.data');

        $listed = collect($organisations)->firstWhere('id', $organisation->id);
        $this->assertNotNull($listed);
        $this->assertArrayNotHasKey('has_sro', $listed);
    }
}
