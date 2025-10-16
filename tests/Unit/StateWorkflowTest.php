<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Models\State;
use App\Models\Project;
use App\Models\Affiliation;
use Illuminate\Support\Str;
use App\Models\Organisation;
use App\Traits\CommonFunctions;
use Tests\Traits\Authorisation;
use Illuminate\Support\Facades\Config;
use KeycloakGuard\ActingAsKeycloakUser;

class StateWorkflowTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;
    use CommonFunctions;
    
    public function setUp(): void
    {
        parent::setUp();
        Config::set('workflow.transitions.enforced', true);
        $this->withUsers();
    }

    public function test_the_application_can_track_user_state(): void
    {
        $user = User::where('user_group', 'USERS')->first();
        $user->setState(State::STATE_PENDING);

        $this->assertDatabaseHas('model_states', [
            'state_id' => State::where('slug', State::STATE_PENDING)->first()->id,
            'stateable_id' => $user->id,
        ]);

        $this->assertTrue($user->getState() === State::STATE_PENDING);
        $this->assertTrue($user->canTransitionTo(State::STATE_VALIDATED) === false);
    }

    public function test_the_application_can_move_users_through_all_logical_states(): void
    {
        $user = User::where('user_group', 'USERS')->first();
        $user->setState(State::STATE_PENDING); // Original State

        $this->assertDatabaseHas('model_states', [
            'state_id' => State::where('slug', State::STATE_PENDING)->first()->id,
            'stateable_id' => $user->id,
        ]);

        $this->assertTrue($user->getState() === State::STATE_PENDING);
        $this->assertTrue($user->canTransitionTo(State::STATE_FORM_RECEIVED) === true);

        $user->transitionTo(State::STATE_FORM_RECEIVED);

        $this->assertDatabaseHas('model_states', [
            'state_id' => State::where('slug', State::STATE_FORM_RECEIVED)->first()->id,
            'stateable_id' => $user->id,
        ]);

        $this->assertTrue($user->getState() === State::STATE_FORM_RECEIVED);

        $user->transitionTo(State::STATE_VALIDATION_IN_PROGRESS);

        $this->assertDatabaseHas('model_states', [
            'state_id' => State::where('slug', State::STATE_VALIDATION_IN_PROGRESS)->first()->id,
            'stateable_id' => $user->id,
        ]);

        $this->assertTrue($user->getState() === State::STATE_VALIDATION_IN_PROGRESS);

        $user->transitionTo(State::STATE_MORE_USER_INFO_REQ);

        $this->assertDatabaseHas('model_states', [
            'state_id' => State::where('slug', State::STATE_MORE_USER_INFO_REQ)->first()->id,
            'stateable_id' => $user->id,
        ]);

        $this->assertTrue($user->getState() === State::STATE_MORE_USER_INFO_REQ);

        $user->transitionTo(State::STATE_ESCALATE_VALIDATION);

        $this->assertDatabaseHas('model_states', [
            'state_id' => State::where('slug', State::STATE_ESCALATE_VALIDATION)->first()->id,
            'stateable_id' => $user->id,
        ]);

        $this->assertTrue($user->getState() === State::STATE_ESCALATE_VALIDATION);

        $user->transitionTo(State::STATE_VALIDATED);

        $this->assertDatabaseHas('model_states', [
            'state_id' => State::where('slug', State::STATE_VALIDATED)->first()->id,
            'stateable_id' => $user->id,
        ]);

        $this->assertTrue($user->getState() === State::STATE_VALIDATED);
    }

    public function test_the_application_can_track_project_state(): void
    {
        $proj = Project::where('id', 1)->first();
        $proj->setState(State::STATE_PENDING);

        $this->assertDatabaseHas('model_states', [
            'state_id' => State::where('slug', State::STATE_PENDING)->first()->id,
            'stateable_id' => $proj->id,
        ]);

        $this->assertTrue($proj->getState() === State::STATE_PENDING);
        $this->assertTrue($proj->canTransitionTo(State::STATE_VALIDATED) === false);
    }

    public function test_the_application_can_track_organisation_state(): void
    {
        $org = Organisation::where('id', 1)->first();
        $org->setState(State::STATE_PENDING);

        $this->assertDatabaseHas('model_states', [
            'state_id' => State::where('slug', State::STATE_PENDING)->first()->id,
            'stateable_id' => $org->id,
        ]);


        $this->assertTrue($org->getState() === State::STATE_PENDING);
        $this->assertTrue($org->canTransitionTo(State::STATE_VALIDATED) === false);
    }

    public function test_the_application_can_track_affiliation_state_non_current_employer(): void
    {
        $affiliation = Affiliation::where('id', 1)->first();

        $this->assertTrue($affiliation->getState() === State::STATE_AFFILIATION_PENDING);
    }

    public function test_the_application_can_track_affiliation_state_current_employer_email_verified(): void
    {
        Affiliation::where('id', 1)->update([
            'is_verified' => 1,
            'current_employer' => 1,
        ]);

        $affiliation = Affiliation::where('id', 1)->first();
        
        $this->assertTrue($affiliation->getState() === State::STATE_AFFILIATION_PENDING);

        Affiliation::where('id', 1)->update([
            'is_verified' => 0,
            'current_employer' => 0,
        ]);
    }

    public function test_the_application_can_track_affiliation_state_current_employer_email_unverified_organisation_claimed(): void
    {
        Affiliation::where('id', 1)->update([
            'is_verified' => 0,
            'current_employer' => 0,
        ]);
        $affiliation = Affiliation::where('id', 1)->first();
        $organisationId = $affiliation->organisation_id;
        Organisation::where('id', $organisationId)->update([
            'unclaimed' => 0,
        ]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                "/api/v1/affiliations/1",
                [
                    'member_id' => 'A1234567',
                    'organisation_id' => 1,
                    'current_employer' => 1,
                    'relationship' => 'employee'
                ]
            );

        $affiliation = Affiliation::where('id', 1)->first();
        $affiliation->transitionTo(State::STATE_AFFILIATION_EMAIL_VERIFY);
        $verficationCode = $affiliation->verification_code;
        $organisationId = $affiliation->organisation_id;
        
        $this->assertTrue($affiliation->getState() === State::STATE_AFFILIATION_EMAIL_VERIFY);
        $this->assertTrue($affiliation->canTransitionTo(State::STATE_AFFILIATION_PENDING) === true);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                "/api/v1/affiliations/verify_email/{$verficationCode}",
                []
            );

        $affiliation = Affiliation::where('id', 1)->first();
        $this->assertTrue($affiliation->is_verified === true);
        $this->assertTrue($affiliation->getState() === State::STATE_AFFILIATION_PENDING);

        $this->assertDatabaseHas('model_states', [
            'state_id' => State::where('slug', State::STATE_AFFILIATION_PENDING)->first()->id,
            'stateable_id' => $affiliation->id,
        ]);

        $this->assertTrue($affiliation->canTransitionTo(State::STATE_AFFILIATION_APPROVED) === true);
        $this->assertTrue($affiliation->canTransitionTo(State::STATE_AFFILIATION_REJECTED) === true);
        $this->assertTrue($affiliation->canTransitionTo(State::STATE_AFFILIATION_EMAIL_VERIFY) === true);
    }
}
