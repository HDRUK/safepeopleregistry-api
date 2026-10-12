<?php

namespace Tests\Unit;

use App\Models\Organisation;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrganisationHasSroTest extends TestCase
{
    public static function organisationMembers(): array
    {
        $claimedSro = ['is_sro' => 1, 'is_delegate' => 0, 'unclaimed' => 0];

        return [
            'no users' => [[], false],
            'only delegates' => [
                [
                    ['is_sro' => 0, 'is_delegate' => 1, 'unclaimed' => 0],
                    ['is_sro' => 0, 'is_delegate' => 0, 'unclaimed' => 0],
                ],
                false,
            ],
            'unclaimed sro' => [[['is_sro' => 1, 'is_delegate' => 0, 'unclaimed' => 1]], false],
            'claimed sro' => [[$claimedSro], true],
            'claimed sro with both flags' => [[['is_sro' => 1, 'is_delegate' => 1, 'unclaimed' => 0]], true],
            'claimed sro outside the organisations group' => [
                [[...$claimedSro, 'user_group' => User::GROUP_USERS]],
                false,
            ],
        ];
    }

    #[DataProvider('organisationMembers')]
    public function test_has_sro_only_when_a_claimed_sro_belongs_to_the_organisation(
        array $members,
        bool $expected
    ): void {
        $organisation = Organisation::factory()->create();

        foreach ($members as $attributes) {
            User::factory()->create([
                'user_group' => User::GROUP_ORGANISATIONS,
                'organisation_id' => $organisation->id,
                ...$attributes,
            ]);
        }

        $this->assertSame($expected, $organisation->hasSro());
    }

    public function test_a_claimed_sro_in_another_organisation_does_not_count(): void
    {
        $organisation = Organisation::factory()->create();
        User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => Organisation::factory()->create()->id,
            'is_sro' => 1,
            'unclaimed' => 0,
        ]);

        $this->assertFalse($organisation->hasSro());
    }

    public function test_has_sro_once_the_invited_sro_claims_their_account(): void
    {
        $organisation = Organisation::factory()->create();
        $sro = User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $organisation->id,
            'is_sro' => 1,
            'unclaimed' => 1,
        ]);

        $this->assertFalse($organisation->hasSro());

        $sro->update(['unclaimed' => 0]);

        $this->assertTrue($organisation->hasSro());
    }
}
