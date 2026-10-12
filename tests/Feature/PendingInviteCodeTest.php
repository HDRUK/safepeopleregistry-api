<?php

namespace Tests\Feature;

use App\Models\PendingInvite;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Tests\TestCase;

class PendingInviteCodeTest extends TestCase
{
    private function createInvite(?string $inviteCode): PendingInvite
    {
        return PendingInvite::create([
            'user_id' => User::factory()->create()->id,
            'status' => PendingInvite::STATE_PENDING,
            'invite_code' => $inviteCode,
        ]);
    }

    public function test_an_invite_code_cannot_be_shared_by_two_invites(): void
    {
        $this->createInvite('6f1c1c9e-4b6e-4c1a-9a43-2d7c1e0b5f10');

        $this->expectException(UniqueConstraintViolationException::class);

        $this->createInvite('6f1c1c9e-4b6e-4c1a-9a43-2d7c1e0b5f10');
    }

    public function test_invites_without_a_code_can_coexist(): void
    {
        $first = $this->createInvite(null);
        $second = $this->createInvite(null);

        $this->assertTrue($first->exists);
        $this->assertTrue($second->exists);
    }
}
