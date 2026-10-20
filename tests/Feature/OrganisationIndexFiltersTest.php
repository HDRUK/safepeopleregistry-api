<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\State;
use App\Models\User;
use KeycloakGuard\ActingAsKeycloakUser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class OrganisationIndexFiltersTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/organisations';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    /**
     * Fixture Organisation ids returned for the given query. Seeded Organisations are excluded,
     * so assertions are exact over the fixtures alone.
     *
     * @param array<string, int> $fixtures
     * @return array<int, string> fixture keys, sorted
     */
    private function fixturesReturnedFor(string $query, array $fixtures): array
    {
        $ids = $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . "?{$query}&per_page=1000")
            ->assertStatus(200)
            ->json('data.data.*.id');

        $keys = array_keys(array_intersect($fixtures, $ids));
        sort($keys);

        return $keys;
    }

    private function createOrganisation(bool $unclaimed = false, ?string $state = null, bool $systemApproved = false): Organisation
    {
        $organisation = Organisation::factory()->create([
            'unclaimed' => $unclaimed,
            'system_approved' => $systemApproved,
        ]);

        if ($state) {
            $organisation->setState($state);
        }

        return $organisation;
    }

    private function createOrganisationUser(Organisation $organisation, bool $isSro, bool $unclaimed): User
    {
        return User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $organisation->id,
            'is_sro' => $isSro,
            'is_delegate' => !$isSro,
            'unclaimed' => $unclaimed,
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function createAccountStatusFixtures(): array
    {
        return [
            'created' => $this->createOrganisation(unclaimed: false)->id,
            // Claiming wins over any invited state, as in the web app.
            'createdWithInvitedState' => $this->createOrganisation(
                unclaimed: false,
                state: State::STATE_ORGANISATION_INVITED_BY_ADMIN
            )->id,
            'invitedByAdmin' => $this->createOrganisation(
                unclaimed: true,
                state: State::STATE_ORGANISATION_INVITED_BY_ADMIN
            )->id,
            'invitedByNonAdmin' => $this->createOrganisation(
                unclaimed: true,
                state: State::STATE_ORGANISATION_INVITED_BY_NONADMIN
            )->id,
            'placeholder' => $this->createOrganisation(
                unclaimed: true,
                state: State::STATE_ORGANISATION_PLACEHOLDER
            )->id,
            'placeholderWithoutState' => $this->createOrganisation(unclaimed: true)->id,
        ];
    }

    /**
     * @return array<string, array{string, array<int, string>}>
     */
    public static function accountStatusProvider(): array
    {
        return [
            'account created' => ['organisation_account_created', ['created', 'createdWithInvitedState']],
            'invited by admin' => ['organisation_invited_by_admin', ['invitedByAdmin']],
            'invited by non-admin' => ['organisation_invited_by_nonadmin', ['invitedByNonAdmin']],
            'placeholder' => ['organisation_placeholder', ['placeholder', 'placeholderWithoutState']],
        ];
    }

    /**
     * @param array<int, string> $expected
     */
    #[DataProvider('accountStatusProvider')]
    public function test_account_status_filter_returns_only_matching_organisations(string $status, array $expected): void
    {
        $fixtures = $this->createAccountStatusFixtures();

        $this->assertSame($expected, $this->fixturesReturnedFor("account_status={$status}", $fixtures));
    }

    /**
     * @return array<string, int>
     */
    private function createSroStatusFixtures(): array
    {
        $claimed = $this->createOrganisation();
        $this->createOrganisationUser($claimed, isSro: true, unclaimed: false);

        $invited = $this->createOrganisation();
        $this->createOrganisationUser($invited, isSro: true, unclaimed: true);

        // A replacement SRO has been invited, but the current SRO is still claimed.
        $claimedAndInvited = $this->createOrganisation();
        $this->createOrganisationUser($claimedAndInvited, isSro: true, unclaimed: false);
        $this->createOrganisationUser($claimedAndInvited, isSro: true, unclaimed: true);

        $delegatesOnly = $this->createOrganisation();
        $this->createOrganisationUser($delegatesOnly, isSro: false, unclaimed: false);

        // is_sro alone does not make an SRO: User::sros() also needs the organisations group.
        $sroOutsideOrganisationsGroup = $this->createOrganisation();
        User::factory()->create([
            'user_group' => User::GROUP_USERS,
            'organisation_id' => $sroOutsideOrganisationsGroup->id,
            'is_sro' => true,
            'unclaimed' => true,
        ]);

        return [
            'claimed' => $claimed->id,
            'invited' => $invited->id,
            'claimedAndInvited' => $claimedAndInvited->id,
            'delegatesOnly' => $delegatesOnly->id,
            'noUsers' => $this->createOrganisation()->id,
            'sroOutsideOrganisationsGroup' => $sroOutsideOrganisationsGroup->id,
        ];
    }

    /**
     * @return array<string, array{string, array<int, string>}>
     */
    public static function sroStatusProvider(): array
    {
        return [
            'claimed' => ['claimed', ['claimed', 'claimedAndInvited']],
            'invited' => ['invited', ['invited']],
            'none' => ['none', ['delegatesOnly', 'noUsers', 'sroOutsideOrganisationsGroup']],
        ];
    }

    /**
     * @param array<int, string> $expected
     */
    #[DataProvider('sroStatusProvider')]
    public function test_sro_status_filter_returns_only_matching_organisations(string $status, array $expected): void
    {
        $fixtures = $this->createSroStatusFixtures();

        $this->assertSame($expected, $this->fixturesReturnedFor("sro_status={$status}", $fixtures));
    }

    /**
     * @return array<int, array<string, mixed>> the fixture Organisations in the response, keyed by id
     */
    private function fixtureOrganisationsFor(string $query, array $fixtures): array
    {
        $organisations = $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . "?{$query}&per_page=1000")
            ->assertStatus(200)
            ->json('data.data');

        return collect($organisations)
            ->whereIn('id', array_values($fixtures))
            ->keyBy('id')
            ->all();
    }

    public function test_index_reports_each_organisations_sro_status(): void
    {
        $fixtures = $this->createSroStatusFixtures();
        $organisations = $this->fixtureOrganisationsFor('', $fixtures);

        $this->assertSame(
            [
                'claimed' => 'claimed',
                'invited' => 'invited',
                'claimedAndInvited' => 'claimed',
                'delegatesOnly' => 'none',
                'noUsers' => 'none',
                'sroOutsideOrganisationsGroup' => 'none',
            ],
            array_map(fn (int $id) => $organisations[$id]['sro_status'], $fixtures)
        );
    }

    #[DataProvider('sroStatusProvider')]
    public function test_reported_sro_status_matches_the_sro_status_filter(string $status): void
    {
        $fixtures = $this->createSroStatusFixtures();
        $organisations = $this->fixtureOrganisationsFor("sro_status={$status}", $fixtures);

        $this->assertNotEmpty($organisations);
        $this->assertSame(
            [$status],
            array_values(array_unique(array_column($organisations, 'sro_status')))
        );
    }

    public function test_system_approved_filter_returns_only_matching_organisations(): void
    {
        $fixtures = [
            'approved' => $this->createOrganisation(systemApproved: true)->id,
            'notApproved' => $this->createOrganisation(systemApproved: false)->id,
        ];

        $this->assertSame(['approved'], $this->fixturesReturnedFor('system_approved=1', $fixtures));
        $this->assertSame(['notApproved'], $this->fixturesReturnedFor('system_approved=0', $fixtures));
    }

    public function test_filters_combine_so_every_filter_must_match(): void
    {
        $approvedClaimed = $this->createOrganisation(systemApproved: true);
        $this->createOrganisationUser($approvedClaimed, isSro: true, unclaimed: false);

        $notApprovedClaimed = $this->createOrganisation(systemApproved: false);
        $this->createOrganisationUser($notApprovedClaimed, isSro: true, unclaimed: false);

        $approvedPlaceholder = $this->createOrganisation(unclaimed: true, systemApproved: true);

        $fixtures = [
            'approvedClaimed' => $approvedClaimed->id,
            'notApprovedClaimed' => $notApprovedClaimed->id,
            'approvedPlaceholder' => $approvedPlaceholder->id,
        ];

        $this->assertSame(
            ['approvedClaimed'],
            $this->fixturesReturnedFor(
                'system_approved=1&sro_status=claimed&account_status=organisation_account_created',
                $fixtures
            )
        );
    }

    public function test_omitting_filters_returns_every_organisation(): void
    {
        $fixtures = [
            ...$this->createAccountStatusFixtures(),
            ...$this->createSroStatusFixtures(),
        ];
        $expected = array_keys($fixtures);
        sort($expected);

        $this->assertSame($expected, $this->fixturesReturnedFor('', $fixtures));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidFilterProvider(): array
    {
        return [
            'unknown account status' => ['account_status=organisation_registered'],
            'unknown sro status' => ['sro_status=approved'],
            'non-boolean system approved' => ['system_approved=maybe'],
        ];
    }

    #[DataProvider('invalidFilterProvider')]
    public function test_invalid_filter_values_are_rejected(string $query): void
    {
        $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . "?{$query}")
            ->assertStatus(400);
    }
}
