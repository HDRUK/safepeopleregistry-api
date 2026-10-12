<?php

namespace Tests\Feature;

use App\Models\Affiliation;
use App\Models\Organisation;
use App\Models\PendingInvite;
use App\Models\Registry;
use App\Models\State;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserObserverClaimTest extends TestCase
{
    private User $researcher;
    private Affiliation $affiliation;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $organisation = Organisation::factory()->create();
        $registry = Registry::factory()->create();

        $this->researcher = User::factory()->create([
            'user_group' => User::GROUP_USERS,
            'registry_id' => $registry->id,
            'keycloak_id' => null,
            'unclaimed' => 1,
        ]);

        PendingInvite::create([
            'user_id' => $this->researcher->id,
            'organisation_id' => $organisation->id,
            'status' => PendingInvite::STATE_PENDING,
            'type' => 'organisation_user_invite',
        ]);

        $this->affiliation = Affiliation::factory()->create([
            'organisation_id' => $organisation->id,
            'registry_id' => $registry->id,
        ]);
        $this->affiliation->setState(State::STATE_AFFILIATION_INVITED);
    }

    public function test_claiming_an_invited_researcher_moves_their_affiliation_on(): void
    {
        $this->withTemporaryObservers(
            fn () => $this->researcher->fresh()->update(['unclaimed' => false])
        );

        $this->assertSame(
            State::STATE_AFFILIATION_INFO_REQUIRED,
            $this->affiliation->fresh()->getState()
        );
    }

    public function test_updating_an_invited_researcher_without_claiming_leaves_their_affiliation_invited(): void
    {
        $this->withTemporaryObservers(
            fn () => $this->researcher->fresh()->update(['role' => 'Data Analyst'])
        );

        $this->assertSame(
            State::STATE_AFFILIATION_INVITED,
            $this->affiliation->fresh()->getState()
        );
    }
}
