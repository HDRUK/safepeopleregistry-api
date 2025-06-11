<?php

namespace App\Traits;

use Exception;
use App\Models\State;
use App\Models\ModelState;

/**
 * @property-read \App\Models\ModelState|null $modelState
 */
trait StateWorkflow
{
    protected array $transitions = [
        State::USER_REGISTERED => [
            State::USER_PENDING,
        ],
        State::USER_PENDING => [
            State::PROJECT_USER_FORM_RECEIVED,
            State::PROJECT_USER_VALIDATION_IN_PROGRESS,
        ],
        State::PROJECT_USER_FORM_RECEIVED => [
            State::PROJECT_USER_VALIDATION_IN_PROGRESS,
            State::PROJECT_USER_MORE_USER_INFO_REQ,
        ],
        State::PROJECT_USER_VALIDATION_IN_PROGRESS => [
            State::PROJECT_USER_VALIDATION_COMPLETE,
            State::PROJECT_USER_MORE_USER_INFO_REQ,
            State::PROJECT_USER_ESCALATE_VALIDATION,
            State::PROJECT_USER_VALIDATED,
        ],
        State::PROJECT_USER_VALIDATION_COMPLETE => [
            State::PROJECT_USER_ESCALATE_VALIDATION,
            State::PROJECT_USER_VALIDATED,
        ],
        State::PROJECT_USER_MORE_USER_INFO_REQ => [
            State::PROJECT_USER_ESCALATE_VALIDATION,
            State::PROJECT_USER_VALIDATED,
        ],
        State::PROJECT_USER_ESCALATE_VALIDATION => [
            State::PROJECT_USER_VALIDATED,
        ],
        State::PROJECT_USER_VALIDATED => [],
        State::PROJECT_PENDING => [
            State::PROJECT_PENDING,
            State::PROJECT_APPROVED,
        ],
        State::PROJECT_APPROVED => [
            State::PROJECT_APPROVED,
            State::PROJECT_COMPLETED
        ],
        State::PROJECT_COMPLETED => [
            State::PROJECT_COMPLETED
        ],
        State::AFFILIATION_INVITED => [
            State::AFFILIATION_PENDING
        ],
        State::AFFILIATION_PENDING => [
            State::AFFILIATION_APPROVED,
            State::AFFILIATION_REJECTED
        ],
        State::AFFILIATION_APPROVED => [
            State::AFFILIATION_REJECTED
        ],
        State::AFFILIATION_REJECTED => [
            State::AFFILIATION_APPROVED
        ]
    ];

    public function modelState()
    {
        return $this->morphOne(ModelState::class, 'stateable');
    }

    public function setState(string $stateSlug)
    {
        $state = State::where('slug', $stateSlug)->firstOrFail();

        if ($this->modelState) {
            $this->modelState->update(['state_id' => $state->id]);
        } else {
            $this->modelState()->create(['state_id' => $state->id]);
        }

        // Reload the relationship. To note, a call to ->refresh() would do the
        // same thing, but refresh queries for the entire model and relationships,
        // whereas this has a far smaller footprint for our needs
        $this->load('modelState');
    }

    public function getState(): ?string
    {
        return $this->modelState ? $this->modelState->state->slug : null;
    }

    public function isInState(string $stateSlug): bool
    {
        return $this->modelState && $this->modelState->state->slug === $stateSlug;
    }

    public function canTransitionTo(string $newStateSlug): bool
    {
        $currentState = $this->getState();
        return (isset($this->transitions[$currentState]) && in_array($newStateSlug, $this->transitions[$currentState]));
    }

    public function transitionTo(string $newStateSlug)
    {
        if (!$this->canTransitionTo($newStateSlug)) {
            throw new Exception('invalid state transition');
        }
        $this->setState($newStateSlug);
    }

    public function pickTransitions(array $states): array
    {
        $transitions = [];

        foreach ($states as $state) {
            $transitions[$state] = $this->transitions[$state];
        }

        return $transitions;
    }
}
