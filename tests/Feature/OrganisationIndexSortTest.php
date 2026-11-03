<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\PendingInvite;
use App\Models\State;
use App\Models\User;
use Carbon\Carbon;
use KeycloakGuard\ActingAsKeycloakUser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use TriggerEmail;

class OrganisationIndexSortTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/organisations';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    private function day(int $day): Carbon
    {
        return Carbon::create(2026, 1, $day, 9);
    }

    /**
     * Fixture keys in the order returned for the given sort. Seeded Organisations are excluded,
     * so assertions are exact over the fixtures alone.
     *
     * @param array<string, int> $fixtures
     * @return array<int, string>
     */
    private function fixtureOrderFor(string $sort, array $fixtures): array
    {
        $ids = $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . "?sort={$sort}&per_page=1000")
            ->assertStatus(200)
            ->json('data.data.*.id');

        $keysById = array_flip($fixtures);

        return array_values(array_map(
            fn (int $id) => $keysById[$id],
            array_filter($ids, fn (int $id) => isset($keysById[$id]))
        ));
    }

    private function statusChangedAtFor(Organisation $organisation): Carbon
    {
        $row = collect(
            $this->actingAs($this->admin)
                ->json('GET', self::TEST_URL . '?per_page=1000')
                ->assertStatus(200)
                ->json('data.data')
        )->firstWhere('id', $organisation->id);

        return Carbon::parse($row['status_changed_at']);
    }

    private function createOrganisationOn(int $day): Organisation
    {
        $this->travelTo($this->day($day));

        return Organisation::factory()->create(['unclaimed' => true, 'system_approved' => false]);
    }

    private function createInvite(Organisation $organisation, int $day, string $type, bool $isSro = false): PendingInvite
    {
        $this->travelTo($this->day($day));

        $invitee = User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $organisation->id,
            'is_sro' => $isSro,
            'is_delegate' => !$isSro,
            'unclaimed' => true,
            'keycloak_id' => null,
        ]);

        return PendingInvite::create([
            'user_id' => $invitee->id,
            'organisation_id' => $organisation->id,
            'status' => PendingInvite::STATE_PENDING,
            'type' => $type,
            'invite_sent_at' => Carbon::now(),
        ]);
    }

    private function createSroInvite(Organisation $organisation, int $day): PendingInvite
    {
        return $this->createInvite($organisation, $day, 'sro_invite', isSro: true);
    }

    /**
     * Each fixture's latest status change is a different kind of change, on a different day.
     *
     * @return array<string, int>
     */
    private function createFixtures(): array
    {
        $createdOnly = $this->createOrganisationOn(1);

        $stateChanged = $this->createOrganisationOn(2);
        $this->travelTo($this->day(6));
        $stateChanged->setState(State::STATE_ORGANISATION_INVITED_BY_ADMIN);

        $approved = $this->createOrganisationOn(3);
        $this->travelTo($this->day(7));
        $approved->system_approved = true;
        $approved->system_approved_at = Carbon::now();
        $approved->save();

        $sroInvited = $this->createOrganisationOn(4);
        $this->createSroInvite($sroInvited, 8);

        $sroClaimed = $this->createOrganisationOn(5);
        $invite = $this->createSroInvite($sroClaimed, 9);
        $this->travelTo($this->day(10));
        $invite->update(['invite_accepted_at' => Carbon::now(), 'status' => PendingInvite::STATE_COMPLETE]);

        $this->travelBack();

        return [
            'createdOnly' => $createdOnly->id,
            'stateChanged' => $stateChanged->id,
            'approved' => $approved->id,
            'sroInvited' => $sroInvited->id,
            'sroClaimed' => $sroClaimed->id,
        ];
    }

    public function test_sorts_by_latest_status_change_descending(): void
    {
        $fixtures = $this->createFixtures();

        $this->assertSame(
            ['sroClaimed', 'sroInvited', 'approved', 'stateChanged', 'createdOnly'],
            $this->fixtureOrderFor('status_changed_at:desc', $fixtures)
        );
    }

    public function test_sorts_by_latest_status_change_ascending(): void
    {
        $fixtures = $this->createFixtures();

        $this->assertSame(
            ['createdOnly', 'stateChanged', 'approved', 'sroInvited', 'sroClaimed'],
            $this->fixtureOrderFor('status_changed_at:asc', $fixtures)
        );
    }

    public function test_index_reports_each_organisations_latest_status_change(): void
    {
        $fixtures = $this->createFixtures();

        $expectedDays = [
            'createdOnly' => 1,
            'stateChanged' => 6,
            'approved' => 7,
            'sroInvited' => 8,
            'sroClaimed' => 10,
        ];

        foreach ($expectedDays as $key => $day) {
            $this->assertTrue(
                $this->statusChangedAtFor(Organisation::findOrFail($fixtures[$key]))->equalTo($this->day($day)),
                "{$key} should have last changed status on day {$day}"
            );
        }
    }

    public function test_sending_an_organisation_invite_is_a_status_change(): void
    {
        $organisation = $this->createOrganisationOn(1);
        $this->createInvite($organisation, 4, 'organisation_invite');
        $this->travelBack();

        $this->assertTrue($this->statusChangedAtFor($organisation)->equalTo($this->day(4)));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function resendableInviteProvider(): array
    {
        return [
            'organisation invite' => ['organisation_invite', false],
            'sro invite' => ['sro_invite', true],
        ];
    }

    #[DataProvider('resendableInviteProvider')]
    public function test_resending_an_invite_is_a_status_change(string $type, bool $isSro): void
    {
        TriggerEmail::spy();

        $organisation = $this->createOrganisationOn(1);
        $invite = $this->createInvite($organisation, 2, $type, $isSro);

        $this->travelTo($this->day(20));
        $this->actingAs($this->admin)
            ->json('POST', "/api/v1/pending_invites/{$invite->id}/resend_invite")
            ->assertStatus(200);
        $this->travelBack();

        $this->assertTrue($this->statusChangedAtFor($organisation)->equalTo($this->day(20)));
    }

    public function test_inviting_an_organisations_users_is_not_a_status_change(): void
    {
        $organisation = $this->createOrganisationOn(1);
        $this->createInvite($organisation, 5, 'organisation_user_invite');
        $this->travelBack();

        $this->assertTrue($this->statusChangedAtFor($organisation)->equalTo($this->day(1)));
    }
}
