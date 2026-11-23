<?php

namespace Tests\Feature;

use App\Enums\CustodianRole;
use App\Http\Traits\HmacSigning;
use App\Models\Custodian;
use App\Models\CustodianUser;
use App\Models\User;
use App\Notifications\CustodianCredentialsRotated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use KeycloakGuard\ActingAsKeycloakUser;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Tests\Traits\CustodianActors;

/**
 * A Custodian's admins and non-read-only members can replace its machine-to-machine secret
 * (unique_identifier), e.g. after a leak. The old secret must stop working straight away.
 */
class CustodianCredentialRotationTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;
    use CustodianActors;
    use HmacSigning;

    private Custodian $custodian;

    private User $readOnly;

    private User $approver;

    public function setUp(): void
    {
        parent::setUp();
        $this->withMiddleware();
        $this->withUsers(true);
        Notification::fake();

        $this->custodian = Custodian::findOrFail(
            CustodianUser::findOrFail($this->custodian_admin->custodian_user_id)->custodian_id
        );

        [$this->readOnly] = $this->makeCustodianUserActor($this->custodian->id, CustodianRole::ReadOnly->value);
        [$this->approver] = $this->makeCustodianUserActor($this->custodian->id, CustodianRole::Approver->value);
    }

    public function test_an_organisation_user_cannot_rotate_a_custodians_secret(): void
    {
        $secret = $this->custodian->unique_identifier;

        $this->rotateAs($this->organisation_admin, $this->custodian)->assertStatus(403);

        $this->assertSame($secret, $this->custodian->fresh()->unique_identifier);
    }

    public function test_a_member_of_another_custodian_cannot_rotate_this_custodians_secret(): void
    {
        $secret = $this->custodian->unique_identifier;
        [$otherMember] = $this->makeCustodianUserActor(Custodian::factory()->create()->id, CustodianRole::Admin->value);

        $this->rotateAs($otherMember, $this->custodian)->assertStatus(403);

        $this->assertSame($secret, $this->custodian->fresh()->unique_identifier);
    }

    public function test_a_read_only_member_cannot_rotate_but_an_approver_can(): void
    {
        $secret = $this->custodian->unique_identifier;

        $this->rotateAs($this->readOnly, $this->custodian)->assertStatus(403);
        $this->assertSame($secret, $this->custodian->fresh()->unique_identifier);

        // Shows the 403 came from the read-only check, not from anything else in the request
        $this->rotateAs($this->approver, $this->custodian)->assertStatus(200);
        $this->assertNotSame($secret, $this->custodian->fresh()->unique_identifier);
    }

    public function test_the_activity_log_never_contains_the_secret(): void
    {
        $oldSecret = $this->custodian->unique_identifier;

        $this->rotateAs($this->approver, $this->custodian)->assertStatus(200);

        $logged = json_encode(Activity::all()->toArray());
        $this->assertStringNotContainsString($oldSecret, $logged);
        $this->assertStringNotContainsString($this->custodian->fresh()->unique_identifier, $logged);
    }

    public function test_requests_signed_with_the_old_secret_stop_working(): void
    {
        $path = '/test/custodian-rotation-signed-request';
        Route::middleware(['check.custodian.access', 'verify.custodian.signed.request'])
            ->get($path, fn (Request $request): JsonResponse => response()->json(['ok' => true]));
        $this->custodian->update(['enabled' => true]);
        $oldSecret = $this->custodian->unique_identifier;

        $this->rotateAs($this->approver, $this->custodian)->assertStatus(200);
        $newSecret = $this->custodian->fresh()->unique_identifier;

        $this->getJson($path, $this->signedHeaders($path, $oldSecret))->assertStatus(401);
        $this->getJson($path, $this->signedHeaders($path, $newSecret))->assertStatus(200);
    }

    public function test_rotating_returns_a_new_secret_and_keeps_the_client_id(): void
    {
        $oldSecret = $this->custodian->unique_identifier;
        $clientId = $this->custodian->client_id;

        $response = $this->rotateAs($this->custodian_admin, $this->custodian)->assertStatus(200);

        $fresh = $this->custodian->fresh();
        $this->assertNotSame($oldSecret, $fresh->unique_identifier);
        $this->assertSame($clientId, $fresh->client_id);
        $response->assertJsonPath('data.unique_identifier', $fresh->unique_identifier);
        $response->assertJsonPath('data.client_id', $clientId);
    }

    public function test_a_system_admin_can_rotate_a_custodians_secret(): void
    {
        $secret = $this->custodian->unique_identifier;

        $this->rotateAs($this->admin, $this->custodian)->assertStatus(200);

        $this->assertNotSame($secret, $this->custodian->fresh()->unique_identifier);
    }

    public function test_every_member_of_the_custodian_is_notified_and_no_one_else(): void
    {
        [$otherMember] = $this->makeCustodianUserActor(Custodian::factory()->create()->id, CustodianRole::Admin->value);

        $this->rotateAs($this->approver, $this->custodian)->assertStatus(200);

        Notification::assertSentTo(
            [$this->custodian_admin, $this->readOnly, $this->approver],
            CustodianCredentialsRotated::class
        );
        Notification::assertNotSentTo($otherMember, CustodianCredentialsRotated::class);
    }

    public function test_the_rotation_is_recorded_in_the_activity_log(): void
    {
        $this->rotateAs($this->approver, $this->custodian)->assertStatus(200);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Custodian::class,
            'subject_id' => $this->custodian->id,
            'causer_type' => User::class,
            'causer_id' => $this->approver->id,
            'event' => 'credentials_rotated',
        ]);
    }

    private function rotateAs(User $user, Custodian $custodian): \Illuminate\Testing\TestResponse
    {
        return $this->actingAsKeycloakUser($user, $this->getMockedKeycloakPayload())
            ->actingAs($user)
            ->json('POST', "/api/v1/custodians/{$custodian->id}/credentials/rotate");
    }

    /**
     * @return array<string, string>
     */
    private function signedHeaders(string $path, string $secret): array
    {
        $timestamp = (string) now()->timestamp;

        return [
            'x-client-id' => $this->custodian->client_id,
            'x-timestamp' => $timestamp,
            'x-signature' => $this->generateCanonicalRequestSignature($timestamp, 'GET', $path, [], $secret),
        ];
    }
}
