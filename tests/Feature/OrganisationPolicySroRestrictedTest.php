<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrganisationPolicySroRestrictedTest extends TestCase
{
    private const ABILITY = 'performSroRestrictedAction';

    public static function actorsAndSroStates(): array
    {
        // Expected outcome per actor for an organisation with: no SRO, an unclaimed SRO, a claimed SRO.
        $expectations = [
            'admin' => [true, true, true],
            'sro' => [true, true, true],
            'delegate' => [true, true, false],
            'neither flag' => [true, true, false],
            'delegate in another organisation' => [false, false, false],
            'sro in another organisation' => [false, false, false],
            'researcher in the organisation' => [false, false, false],
            'custodian' => [false, false, false],
        ];
        $sroStates = ['no sro', 'unclaimed sro', 'claimed sro'];

        $cases = [];
        foreach ($expectations as $actor => $outcomes) {
            foreach ($sroStates as $i => $sroState) {
                $cases["{$actor}, {$sroState}"] = [$actor, $sroState, $outcomes[$i]];
            }
        }

        return $cases;
    }

    private function createOrganisationUser(Organisation $organisation, int $isSro, int $isDelegate, int $unclaimed = 0): User
    {
        return User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $organisation->id,
            'is_sro' => $isSro,
            'is_delegate' => $isDelegate,
            'unclaimed' => $unclaimed,
        ]);
    }

    private function createActor(string $actor, Organisation $organisation): User
    {
        return match ($actor) {
            'admin' => User::factory()->create(['user_group' => User::GROUP_ADMINS]),
            'sro' => $this->createOrganisationUser($organisation, isSro: 1, isDelegate: 0),
            'delegate' => $this->createOrganisationUser($organisation, isSro: 0, isDelegate: 1),
            'neither flag' => $this->createOrganisationUser($organisation, isSro: 0, isDelegate: 0),
            'delegate in another organisation' => $this->createOrganisationUser(Organisation::factory()->create(), isSro: 0, isDelegate: 1),
            'sro in another organisation' => $this->createOrganisationUser(Organisation::factory()->create(), isSro: 1, isDelegate: 0),
            'researcher in the organisation' => User::factory()->create([
                'user_group' => User::GROUP_USERS,
                'organisation_id' => $organisation->id,
            ]),
            'custodian' => User::factory()->create(['user_group' => User::GROUP_CUSTODIANS]),
        };
    }

    #[DataProvider('actorsAndSroStates')]
    public function test_sro_restricted_actions_are_limited_to_sros_once_the_organisation_has_one(
        string $actor,
        string $sroState,
        bool $expected
    ): void {
        $organisation = Organisation::factory()->create();

        match ($sroState) {
            'no sro' => null,
            'unclaimed sro' => $this->createOrganisationUser($organisation, isSro: 1, isDelegate: 0, unclaimed: 1),
            'claimed sro' => $this->createOrganisationUser($organisation, isSro: 1, isDelegate: 0),
        };

        $user = $this->createActor($actor, $organisation);

        $this->assertSame($expected, Gate::forUser($user)->allows(self::ABILITY, $organisation));
    }

    public function test_a_delegate_loses_sro_restricted_actions_when_the_invited_sro_claims_their_account(): void
    {
        $organisation = Organisation::factory()->create();
        $delegate = $this->createOrganisationUser($organisation, isSro: 0, isDelegate: 1);
        $sro = $this->createOrganisationUser($organisation, isSro: 1, isDelegate: 0, unclaimed: 1);

        $this->assertTrue(Gate::forUser($delegate)->allows(self::ABILITY, $organisation));

        $sro->update(['unclaimed' => 0]);

        $this->assertFalse(Gate::forUser($delegate)->allows(self::ABILITY, $organisation));
    }
}
