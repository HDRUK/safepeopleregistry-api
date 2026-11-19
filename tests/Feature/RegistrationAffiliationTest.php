<?php

namespace Tests\Feature;

use App\Models\Affiliation;
use App\Models\Organisation;
use App\Models\Registry;
use App\Models\State;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use KeycloakGuard\ActingAsKeycloakUser;
use Tests\TestCase;

/**
 * A researcher must have a current affiliation they have done their part on before they are
 * fully signed up. Their part done consists of providing their affiliation details and verifying their email address.
 * These tests cover how the API reports that, and the existing affiliation endpoints the registration step reuses.
 */
class RegistrationAffiliationTest extends TestCase
{
    use ActingAsKeycloakUser;

    private const AFFILIATIONS_URL = '/api/v1/affiliations';

    private User $researcher;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create(['system_approved' => true]);
        $this->researcher = $this->createResearcher('researcher@example.com');
    }

    private function createResearcher(string $email): User
    {
        $registry = Registry::factory()->create();

        return User::factory()->create([
            'user_group' => User::GROUP_USERS,
            'email' => $email,
            'registry_id' => $registry->id,
            'unclaimed' => 0,
        ]);
    }

    private function createAffiliation(User $user, string $state, array $attributes = []): Affiliation
    {
        $affiliation = Affiliation::factory()->create([
            'organisation_id' => $this->organisation->id,
            'registry_id' => $user->registry_id,
            'current_employer' => true,
            ...$attributes,
        ]);
        $affiliation->setState($state);

        return $affiliation;
    }

    private function affiliationPayload(array $overrides = []): array
    {
        return [
            'organisation_id' => $this->organisation->id,
            'member_id' => 'A1234567',
            'relationship' => 'employee',
            'from' => Carbon::today()->toDateString(),
            'to' => null,
            'department' => null,
            'role' => 'Researcher',
            'email' => $this->researcher->email,
            'ror' => null,
            ...$overrides,
        ];
    }

    private function getMe(User $user): TestResponse
    {
        // /auth/me reads the token's subject, so the user must come from the token, not the database.
        config(['keycloak.load_user_from_database' => false]);
        Auth::forgetGuards();

        return $this->actingAsKeycloakUser(null, ['sub' => $user->keycloak_id])
            ->json('GET', '/api/auth/me');
    }

    public function test_me_returns_the_logged_in_researcher(): void
    {
        $response = $this->getMe($this->researcher);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $this->researcher->id)
            ->assertJsonPath('data.email', 'researcher@example.com')
            ->assertJsonPath('data.user_group', User::GROUP_USERS)
            ->assertJsonPath('data.registry_id', $this->researcher->registry_id)
            ->assertJsonPath('data.status', State::STATE_REGISTERED)
            ->assertJsonMissingPath('data.keycloak_id');
    }

    public function test_creating_an_affiliation_with_the_login_email_leaves_it_pending(): void
    {
        $response = $this->actingAs($this->researcher)
            ->json('POST', self::AFFILIATIONS_URL . "/{$this->researcher->registry_id}", $this->affiliationPayload());

        $response->assertStatus(201);
        $affiliation = Affiliation::findOrFail($response->json('data'));
        $this->assertSame(State::STATE_AFFILIATION_PENDING, $affiliation->getState());
    }

    public function test_creating_an_affiliation_with_another_email_waits_for_verification(): void
    {
        $response = $this->actingAs($this->researcher)
            ->json('POST', self::AFFILIATIONS_URL . "/{$this->researcher->registry_id}", $this->affiliationPayload([
                'email' => 'researcher@university.example.com',
            ]));

        $response->assertStatus(201);
        $affiliation = Affiliation::findOrFail($response->json('data'));
        $this->assertSame(State::STATE_AFFILIATION_EMAIL_VERIFY, $affiliation->getState());
    }

    public function test_completing_an_invited_affiliation_keeps_the_invited_email(): void
    {
        $invited = $this->createAffiliation($this->researcher, State::STATE_AFFILIATION_INFO_REQUIRED, [
            'email' => 'invited@university.example.com',
            // The invite flow leaves the researcher's details blank for them to fill in.
            'member_id' => '',
            'role' => '',
        ]);

        $response = $this->actingAs($this->researcher)
            ->json('PUT', self::AFFILIATIONS_URL . "/{$invited->id}", $this->affiliationPayload([
                'email' => 'someone.else@example.com',
            ]));

        $response->assertStatus(200);
        $invited->refresh();
        $this->assertSame('invited@university.example.com', $invited->email);
        $this->assertSame('A1234567', $invited->member_id);
        $this->assertNotSame(State::STATE_AFFILIATION_INFO_REQUIRED, $invited->getState());
        $this->assertSame(1, Affiliation::where('registry_id', $this->researcher->registry_id)->count());
    }

    public function test_the_owner_can_verify_their_affiliation_email(): void
    {
        $code = (string) Str::uuid();
        $affiliation = $this->createAffiliation($this->researcher, State::STATE_AFFILIATION_EMAIL_VERIFY, [
            'verification_code' => $code,
            'verification_sent_at' => Carbon::now(),
            'is_verified' => 0,
        ]);

        $response = $this->actingAs($this->researcher)
            ->json('PUT', self::AFFILIATIONS_URL . "/verify_email/{$code}");

        $response->assertStatus(200);
        $affiliation->refresh();
        $this->assertTrue((bool) $affiliation->is_verified);
        $this->assertSame(State::STATE_AFFILIATION_PENDING, $affiliation->getState());
    }
}
