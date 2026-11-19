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
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_another_users_code_cannot_verify_their_affiliation(): void
    {
        $code = (string) Str::uuid();
        $affiliation = $this->createAffiliation($this->researcher, State::STATE_AFFILIATION_EMAIL_VERIFY, [
            'verification_code' => $code,
            'verification_sent_at' => Carbon::now(),
            'is_verified' => 0,
        ]);
        $other = $this->createResearcher('other.researcher@example.com');

        $response = $this->actingAs($other)
            ->json('PUT', self::AFFILIATIONS_URL . "/verify_email/{$code}");

        $this->assertFalse($response->isSuccessful());
        $affiliation->refresh();
        $this->assertFalse((bool) $affiliation->is_verified);
        $this->assertSame(State::STATE_AFFILIATION_EMAIL_VERIFY, $affiliation->getState());
    }

    /**
     * One state for each kind of outcome.
     *
     * @return array<string, array{string, bool}>
     */
    public static function affiliationStates(): array
    {
        return [
            'not filled in yet' => [State::STATE_AFFILIATION_INFO_REQUIRED, false],
            'waiting on the researcher to verify their email' => [State::STATE_AFFILIATION_EMAIL_VERIFY, false],
            'waiting on the organisation' => [State::STATE_AFFILIATION_PENDING, true],
            'approved' => [State::STATE_AFFILIATION_APPROVED, true],
            'rejected' => [State::STATE_AFFILIATION_REJECTED, false],
            'ended' => [State::STATE_AFFILIATION_LEFT, false],
        ];
    }

    #[DataProvider('affiliationStates')]
    public function test_only_affiliations_the_researcher_has_provided_information_and_verified_email_address_count(string $state, bool $counts): void
    {
        $this->createAffiliation($this->researcher, $state);

        $this->assertSame($counts, $this->researcher->hasCurrentAffiliation());
    }

    public function test_an_affiliation_whose_end_date_has_passed_does_not_count(): void
    {
        $this->createAffiliation($this->researcher, State::STATE_AFFILIATION_PENDING, [
            'to' => Carbon::yesterday()->toDateString(),
        ]);

        $this->assertFalse($this->researcher->hasCurrentAffiliation());
    }

    public function test_an_affiliation_ending_in_the_future_counts(): void
    {
        $this->createAffiliation($this->researcher, State::STATE_AFFILIATION_PENDING, [
            'to' => Carbon::today()->addMonth()->toDateString(),
        ]);

        $this->assertTrue($this->researcher->hasCurrentAffiliation());
    }

    public function test_a_researcher_with_no_affiliations_has_no_current_affiliation(): void
    {
        $this->assertFalse($this->researcher->hasCurrentAffiliation());
    }

    public function test_another_researchers_affiliation_never_counts(): void
    {
        $other = $this->createResearcher('other.researcher@example.com');
        $this->createAffiliation($other, State::STATE_AFFILIATION_APPROVED);

        $this->assertFalse($this->researcher->hasCurrentAffiliation());
        $this->assertTrue($other->hasCurrentAffiliation());
    }

    public function test_there_is_no_registration_affiliation_when_nothing_needs_acting_on(): void
    {
        $this->createAffiliation($this->researcher, State::STATE_AFFILIATION_PENDING);

        $this->assertNull($this->researcher->registrationAffiliation());
    }

    public function test_the_registration_affiliation_is_the_invited_one_with_its_organisation(): void
    {
        $invited = $this->createAffiliation($this->researcher, State::STATE_AFFILIATION_INFO_REQUIRED);

        $affiliation = $this->researcher->registrationAffiliation();

        $this->assertSame($invited->id, $affiliation?->id);
        $this->assertTrue($affiliation->relationLoaded('organisation'));
        $this->assertSame($this->organisation->id, $affiliation->organisation->id);
    }

    public function test_an_affiliation_waiting_on_email_verification_comes_before_an_invited_one(): void
    {
        $this->createAffiliation($this->researcher, State::STATE_AFFILIATION_INFO_REQUIRED);
        $awaitingVerification = $this->createAffiliation($this->researcher, State::STATE_AFFILIATION_EMAIL_VERIFY);

        $this->assertSame($awaitingVerification->id, $this->researcher->registrationAffiliation()?->id);
    }

    public function test_the_oldest_invited_affiliation_comes_first(): void
    {
        $oldest = $this->createAffiliation($this->researcher, State::STATE_AFFILIATION_INFO_REQUIRED);
        $this->createAffiliation($this->researcher, State::STATE_AFFILIATION_INFO_REQUIRED);

        $this->assertSame($oldest->id, $this->researcher->registrationAffiliation()?->id);
    }

    public function test_another_researchers_affiliation_is_never_the_registration_affiliation(): void
    {
        $other = $this->createResearcher('other.researcher@example.com');
        $this->createAffiliation($other, State::STATE_AFFILIATION_INFO_REQUIRED);

        $this->assertNull($this->researcher->registrationAffiliation());
    }

    public function test_me_reports_a_researcher_without_an_affiliation_as_not_signed_up(): void
    {
        $this->getMe($this->researcher)
            ->assertStatus(200)
            ->assertJsonPath('data.registration_complete', false)
            ->assertJsonPath('data.registration_affiliation', null);
    }

    public function test_me_reports_a_researcher_signed_up_once_they_add_an_affiliation_with_their_login_email(): void
    {
        $this->actingAs($this->researcher)
            ->json('POST', self::AFFILIATIONS_URL . "/{$this->researcher->registry_id}", $this->affiliationPayload())
            ->assertStatus(201);

        $this->getMe($this->researcher)
            ->assertJsonPath('data.registration_complete', true)
            ->assertJsonPath('data.registration_affiliation', null);
    }

    public function test_me_reports_a_researcher_signed_up_only_once_they_verify_another_email(): void
    {
        $response = $this->actingAs($this->researcher)
            ->json('POST', self::AFFILIATIONS_URL . "/{$this->researcher->registry_id}", $this->affiliationPayload([
                'email' => 'researcher@university.example.com',
            ]));
        $affiliation = Affiliation::findOrFail($response->json('data'));

        $this->getMe($this->researcher)
            ->assertJsonPath('data.registration_complete', false)
            ->assertJsonPath('data.registration_affiliation.id', $affiliation->id)
            ->assertJsonPath('data.registration_affiliation.state', State::STATE_AFFILIATION_EMAIL_VERIFY)
            ->assertJsonPath('data.registration_affiliation.email', 'researcher@university.example.com');

        $this->actingAs($this->researcher)
            ->json('PUT', self::AFFILIATIONS_URL . "/verify_email/{$affiliation->verification_code}")
            ->assertStatus(200);

        $this->getMe($this->researcher)
            ->assertJsonPath('data.registration_complete', true)
            ->assertJsonPath('data.registration_affiliation', null);
    }

    public function test_me_prefills_an_invited_researchers_affiliation_until_they_complete_it(): void
    {
        $invited = $this->createAffiliation($this->researcher, State::STATE_AFFILIATION_INFO_REQUIRED, [
            'email' => 'researcher@example.com',
            'member_id' => '',
            'role' => '',
        ]);

        $this->getMe($this->researcher)
            ->assertJsonPath('data.registration_complete', false)
            ->assertJsonPath('data.registration_affiliation', [
                'id' => $invited->id,
                'state' => State::STATE_AFFILIATION_INFO_REQUIRED,
                'organisation_id' => $this->organisation->id,
                'organisation_name' => $this->organisation->organisation_name,
                'email' => 'researcher@example.com',
            ]);

        $this->actingAs($this->researcher)
            ->json('PUT', self::AFFILIATIONS_URL . "/{$invited->id}", $this->affiliationPayload())
            ->assertStatus(200);

        $this->getMe($this->researcher)
            ->assertJsonPath('data.registration_complete', true)
            ->assertJsonPath('data.registration_affiliation', null);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function nonResearcherGroups(): array
    {
        return [
            'organisation' => [User::GROUP_ORGANISATIONS],
            'custodian' => [User::GROUP_CUSTODIANS],
            'admin' => [User::GROUP_ADMINS],
        ];
    }

    #[DataProvider('nonResearcherGroups')]
    public function test_me_always_reports_non_researchers_as_signed_up(string $userGroup): void
    {
        $user = User::factory()->create(['user_group' => $userGroup, 'unclaimed' => 0]);

        $this->getMe($user)
            ->assertStatus(200)
            ->assertJsonPath('data.registration_complete', true)
            ->assertJsonPath('data.registration_affiliation', null);
    }
}
