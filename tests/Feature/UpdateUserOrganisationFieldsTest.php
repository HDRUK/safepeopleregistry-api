<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use App\Notifications\Organisations\OrganisationDelegates;
use Illuminate\Support\Facades\Notification;
use KeycloakGuard\ActingAsKeycloakUser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class UpdateUserOrganisationFieldsTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/users';

    private Organisation $organisation;
    private User $delegate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
        Notification::fake();

        $this->organisation = Organisation::factory()->create();
        $this->delegate = User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $this->organisation->id,
            'is_sro' => 0,
            'is_delegate' => 1,
        ]);
    }

    public static function changes(): array
    {
        return [
            'clearing is_delegate' => [['is_delegate' => 0]],
            'clearing organisation_id' => [['organisation_id' => null]],
            'moving organisation' => [['organisation_id' => 'other']],
        ];
    }

    #[DataProvider('changes')]
    public function test_organisation_membership_cannot_be_changed(array $payload): void
    {
        if (($payload['organisation_id'] ?? null) === 'other') {
            $payload['organisation_id'] = Organisation::factory()->create()->id;
        }

        $this->actingAs($this->admin)
            ->json('PUT', self::TEST_URL . "/{$this->delegate->id}", $payload)
            ->assertStatus(400);

        $delegate = $this->delegate->fresh();
        $this->assertSame($this->organisation->id, $delegate->organisation_id);
        $this->assertSame(1, $delegate->is_delegate);
    }

    public function test_sending_the_current_membership_back_is_accepted(): void
    {
        // Profile forms echo the whole user back with their changes.
        $this->actingAs($this->admin)
            ->json('PUT', self::TEST_URL . "/{$this->delegate->id}", [
                'organisation_id' => $this->organisation->id,
                'is_delegate' => 1,
                'role' => 'Data Manager',
            ])
            ->assertStatus(200);

        $this->assertSame('Data Manager', $this->delegate->fresh()->role);
    }

    public function test_updating_an_sro_does_not_tell_them_they_were_removed_as_a_delegate(): void
    {
        $sro = User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $this->organisation->id,
            'is_sro' => 1,
            'is_delegate' => 0,
        ]);

        $this->actingAs($this->admin)
            ->json('PUT', self::TEST_URL . "/{$sro->id}", ['role' => 'Head of Research'])
            ->assertStatus(200);

        Notification::assertNotSentTo($sro, OrganisationDelegates::class);
    }
}
