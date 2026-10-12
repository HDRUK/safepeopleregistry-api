<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserPolicyOrganisationRoleTest extends TestCase
{
    private const FLAGS = [
        'sro only' => [1, 0],
        'sro and delegate' => [1, 1],
        'delegate only' => [0, 1],
        'neither flag' => [0, 0],
    ];

    public static function sameOrganisationUsers(): array
    {
        $cases = [];

        foreach (self::FLAGS as $actorLabel => [$actorIsSro, $actorIsDelegate]) {
            foreach (self::FLAGS as $targetLabel => [$targetIsSro, $targetIsDelegate]) {
                // SROs can update anyone in their organisation; delegates can only update delegates.
                $expected = $actorIsSro === 1 || $targetIsSro === 0;
                $cases["{$actorLabel} updating {$targetLabel}"] = [
                    [$actorIsSro, $actorIsDelegate],
                    [$targetIsSro, $targetIsDelegate],
                    $expected,
                ];
            }
        }

        return $cases;
    }

    public static function allFlags(): array
    {
        return array_map(fn (array $flags) => [$flags], self::FLAGS);
    }

    private function createOrganisationUser(Organisation $organisation, array $flags): User
    {
        [$isSro, $isDelegate] = $flags;

        return User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $organisation->id,
            'is_sro' => $isSro,
            'is_delegate' => $isDelegate,
        ]);
    }

    #[DataProvider('sameOrganisationUsers')]
    public function test_organisation_users_can_update_users_in_their_organisation_by_role(
        array $actorFlags,
        array $targetFlags,
        bool $expected
    ): void {
        $organisation = Organisation::factory()->create();
        $actor = $this->createOrganisationUser($organisation, $actorFlags);
        $target = $this->createOrganisationUser($organisation, $targetFlags);

        $this->assertSame($expected, Gate::forUser($actor)->allows('update', $target));
    }

    #[DataProvider('allFlags')]
    public function test_organisation_users_cannot_update_users_in_another_organisation(array $actorFlags): void
    {
        $actor = $this->createOrganisationUser(Organisation::factory()->create(), $actorFlags);
        $target = $this->createOrganisationUser(Organisation::factory()->create(), [0, 1]);

        $this->assertFalse(Gate::forUser($actor)->allows('update', $target));
    }

    #[DataProvider('allFlags')]
    public function test_organisation_users_can_update_themselves(array $flags): void
    {
        $user = $this->createOrganisationUser(Organisation::factory()->create(), $flags);

        $this->assertTrue(Gate::forUser($user)->allows('update', $user));
    }
}
