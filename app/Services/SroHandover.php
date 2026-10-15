<?php

namespace App\Services;

use App\Models\Organisation;
use App\Models\User;

/**
 * What changed when an organisation's SRO role passed to someone, so the emails can be sent once
 * the change has committed.
 */
final readonly class SroHandover
{
    public function __construct(
        public Organisation $organisation,
        public int $newSroId,
        // The claimed SRO who lost the role, if there was one. Invited SROs are not counted.
        public ?int $replacedSroId,
        // Who made the change, named in the emails.
        public User $actor,
    ) {
    }
}
