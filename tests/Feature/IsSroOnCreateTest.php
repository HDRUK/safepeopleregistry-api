<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use KeycloakGuard\ActingAsKeycloakUser;
use Laravel\Pennant\Feature;
use PHPUnit\Framework\Attributes\DataProvider;
use RegistryManagementController as RMC;
use Tests\TestCase;
use Tests\Traits\Authorisation;

/**
 * Pins every way is_sro is set when a user is created or invited, so guarding it against
 * mass assignment cannot silently change who becomes an SRO.
 */
class IsSroOnCreateTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const ORGANISATIONS_URL = '/api/v1/organisations';
    public const USERS_URL = '/api/v1/users';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    private function withSroRequirement(bool $enabled, callable $callback): void
    {
        $wasEnabled = Feature::active('SroRequirementEnabled');

        try {
            $enabled
                ? Feature::activate('SroRequirementEnabled')
                : Feature::deactivate('SroRequirementEnabled');

            $callback();
        } finally {
            $wasEnabled
                ? Feature::activate('SroRequirementEnabled')
                : Feature::deactivate('SroRequirementEnabled');
        }
    }

    private function userWithEmail(string $email): User
    {
        return User::where('email', $email)->sole();
    }

    public static function sroRequirement(): array
    {
        return [
            'sro requirement enabled' => [true],
            'sro requirement disabled' => [false],
        ];
    }

    #[DataProvider('sroRequirement')]
    public function test_inviting_an_organisation_makes_the_lead_applicant_an_sro_only_when_required(bool $enabled): void
    {
        $organisation = Organisation::factory()->create([
            'lead_applicant_email' => fake()->unique()->safeEmail(),
        ]);

        $this->withSroRequirement($enabled, function () use ($organisation) {
            $this->actingAs($this->admin)
                ->json('POST', self::ORGANISATIONS_URL . "/{$organisation->id}/invite")
                ->assertStatus(201);
        });

        $this->assertSame($enabled, $this->userWithEmail($organisation->lead_applicant_email)->is_sro);
    }

    #[DataProvider('sroRequirement')]
    public function test_a_custodian_inviting_an_organisation_user_makes_them_an_sro_only_when_required(bool $enabled): void
    {
        $email = fake()->unique()->safeEmail();

        $this->withSroRequirement($enabled, function () use ($email) {
            $this->actingAs($this->custodian_admin)
                ->json('POST', self::ORGANISATIONS_URL . '/1/custodian_invite_user', [
                    'first_name' => 'Jane',
                    'last_name' => 'Doe',
                    'email' => $email,
                    'user_group' => User::GROUP_ORGANISATIONS,
                ])
                ->assertStatus(201);
        });

        $this->assertSame($enabled, $this->userWithEmail($email)->is_sro);
    }

    public function test_an_organisation_inviting_a_user_does_not_make_them_an_sro(): void
    {
        $email = fake()->unique()->safeEmail();

        $this->actingAs($this->organisation_admin)
            ->json('POST', self::ORGANISATIONS_URL . '/1/invite_user', [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => $email,
                'user_group' => User::GROUP_ORGANISATIONS,
                'is_delegate' => 1,
            ])
            ->assertStatus(201);

        $this->assertFalse($this->userWithEmail($email)->is_sro);
    }

    public function test_an_admin_can_create_an_sro(): void
    {
        $email = fake()->unique()->safeEmail();

        $this->actingAs($this->admin)
            ->json('POST', self::USERS_URL, [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => $email,
                'is_sro' => 1,
            ])
            ->assertStatus(201);

        $this->assertTrue($this->userWithEmail($email)->is_sro);
    }

    public function test_re_inviting_an_unclaimed_user_replaces_their_sro_flag(): void
    {
        $organisation = Organisation::factory()->create();
        $invite = fn (int $isSro) => RMC::createUnclaimedUser([
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => 'jane.doe@example.com',
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $organisation->id,
            'is_sro' => $isSro,
        ]);

        $this->assertTrue($invite(1)->fresh()->is_sro);
        $this->assertFalse($invite(0)->fresh()->is_sro);
    }
}
