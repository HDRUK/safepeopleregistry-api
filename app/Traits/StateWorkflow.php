<?php

namespace App\Traits;

use App\Models\State;

/**
 * @property-read \App\Models\ModelState|null $modelState
 */
trait StateWorkflow
{
    use BaseStateWorkflow;

    protected array $transitions = [
        State::USER_REGISTERED => [
            State::USER_PENDING,
        ],
        State::USER_PENDING => [
            State::PROJECT_USER_FORM_RECEIVED,
            State::PROJECT_USER_VALIDATION_IN_PROGRESS,
        ],
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
}
