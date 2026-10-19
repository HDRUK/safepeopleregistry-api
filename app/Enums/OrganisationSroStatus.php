<?php

namespace App\Enums;

/**
 * Whether an Organisation has an SRO, derived from its SRO users.
 */
enum OrganisationSroStatus: string
{
    // An SRO has claimed their account.
    case Claimed = 'claimed';
    // An SRO has been invited but none has claimed their account yet.
    case Invited = 'invited';
    // The Organisation has no SRO.
    case None = 'none';
}
