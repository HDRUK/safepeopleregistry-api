<?php

namespace App\Enums;

use App\Models\State;

/**
 * An Organisation's account status, as shown to admins. Derived from `unclaimed` and the
 * Organisation's model state; mirrors getOrganisationAccountStatus() in the web app.
 */
enum OrganisationAccountStatus: string
{
    case AccountCreated = 'organisation_account_created';
    case InvitedByAdmin = State::STATE_ORGANISATION_INVITED_BY_ADMIN;
    case InvitedByNonAdmin = State::STATE_ORGANISATION_INVITED_BY_NONADMIN;
    case Placeholder = State::STATE_ORGANISATION_PLACEHOLDER;
}
