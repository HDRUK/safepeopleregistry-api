<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\Traits\Authorisation;
use Spatie\Activitylog\Models\Activity;

class AuditLogTest extends TestCase
{
    use Authorisation;

    public const TEST_URL = '/api/v1/';

    public function setUp(): void
    {
        parent::setUp();
        $this->enableObservers();
        $this->withUsers();
    }

    public function test_it_creates_audit_logs_when_a_user_is_created_with_success()
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'POST',
                self::TEST_URL . 'users',
                [
                    'first_name' => fake()->firstname(),
                    'last_name' => fake()->lastname(),
                    'email' => fake()->email(),
                    'provider' => fake()->word(),
                    'provider_sub' => Str::random(10),
                    'public_opt_in' => fake()->randomElement([0, 1]),
                    'declaration_signed' => fake()->randomElement([0, 1]),
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $userId = $response->decodeResponseJson()['data'];
        $this->assertGreaterThan(0, $userId);

        $activity = Activity::where('subject_id', $userId)
            ->where('subject_type', User::class)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals('created', $activity->event);
        $this->assertEquals($userId, $activity->subject_id);
    }
}
