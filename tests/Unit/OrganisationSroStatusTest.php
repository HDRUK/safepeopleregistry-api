<?php

namespace Tests\Unit;

use App\Enums\OrganisationSroStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OrganisationSroStatusTest extends TestCase
{
    /**
     * @return array<string, array{bool, bool, OrganisationSroStatus}>
     */
    public static function sroFlags(): array
    {
        return [
            'claimed sro and an unclaimed sro' => [true, true, OrganisationSroStatus::Claimed],
            'claimed sro, no unclaimed sro' => [true, false, OrganisationSroStatus::Claimed],
            'unclaimed sro only' => [false, true, OrganisationSroStatus::Invited],
            'no sro' => [false, false, OrganisationSroStatus::None],
        ];
    }

    #[DataProvider('sroFlags')]
    public function test_from_sro_flags(bool $hasClaimedSro, bool $hasUnclaimedSro, OrganisationSroStatus $expected): void
    {
        $this->assertSame($expected, OrganisationSroStatus::fromSroFlags($hasClaimedSro, $hasUnclaimedSro));
    }
}
