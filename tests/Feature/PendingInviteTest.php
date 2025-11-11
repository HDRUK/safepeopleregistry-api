<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\Authorisation;

class PendingInviteTest extends TestCase
{
    use Authorisation;

    public const TEST_URL = '/api/v1/pending_invites';
    
    public function setUp(): void
    {
        parent::setUp();
        $this->withMiddleware();
        $this->withUsers();
    }

    public function test_the_application_can_list_pending_invites(): void
    {
        $response = $this->actingAs($this->organisation_admin)
            ->json(
                'GET',
                self::TEST_URL
            );
        $response->assertStatus(200);
    }
}