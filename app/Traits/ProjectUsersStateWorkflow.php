<?php

namespace App\Traits;

use Exception;
use App\Models\State;
use App\Models\ModelState;

/**
 * @property-read \App\Models\ModelState|null $modelState
 */
trait ProjectUsersStateWorkflow
{
    use BaseStateWorkflow;

    protected array $transitions = [
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
    ];
}
