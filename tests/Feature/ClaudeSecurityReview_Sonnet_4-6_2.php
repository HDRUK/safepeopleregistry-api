<?php

/**
 * Security regression tests — Controller Authorization
 *
 * Covers:
 *   Vuln 2 — Any authenticated user could reset any other user's Keycloak password
 *            (UserController::resetPasswordById — no Gate check)
 *   Vuln 7 — Any authenticated user could trigger Keycloak verification emails for
 *            arbitrary addresses and enumerate registered emails
 *            (UserController::resendKeycloakVerificationEmailById — no Gate check)
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use KeycloakGuard\ActingAsKeycloakUser;
use Tests\Traits\Authorisation;

uses(ActingAsKeycloakUser::class);
uses(Authorisation::class);

beforeEach(function () {
    $this->withUsers();
});

// ---------------------------------------------------------------------------
// Vuln 2: resetPasswordById — authorization checks
// ---------------------------------------------------------------------------

it('blocks a regular user from resetting another users Keycloak password', function () {
    $otherUser = User::where('user_group', User::GROUP_USERS)
        ->where('id', '!=', $this->user->id)
        ->first();

    if (!$otherUser) {
        $otherUser = User::factory()->create(['user_group' => User::GROUP_USERS]);
    }

    $response = $this->actingAs($this->user)
        ->json('PUT', "/api/v1/users/{$otherUser->id}/keycloak/reset_password");

    $response->assertStatus(403);
});

it('blocks an organisation admin from resetting a user outside their org via reset_password', function () {
    $otherUser = User::where('user_group', User::GROUP_USERS)->first();

    $response = $this->actingAs($this->organisation_admin)
        ->json('PUT', "/api/v1/users/{$otherUser->id}/keycloak/reset_password");

    $response->assertStatus(403);
});

it('allows a user to trigger a password reset for their own account', function () {
    Http::fake([
        '*/realms/*/protocol/openid-connect/token' => Http::response(
            ['access_token' => 'fake-admin-token'],
            200
        ),
    ]);

    \Keycloak::shouldReceive('resetUserPassword')
        ->once()
        ->andReturn(true);

    $response = $this->actingAs($this->user)
        ->json('PUT', "/api/v1/users/{$this->user->id}/keycloak/reset_password");

    $response->assertStatus(200);
});

it('allows an admin to trigger a password reset for any user', function () {
    $targetUser = User::where('user_group', User::GROUP_USERS)->first();

    Http::fake([
        '*/realms/*/protocol/openid-connect/token' => Http::response(
            ['access_token' => 'fake-admin-token'],
            200
        ),
    ]);

    \Keycloak::shouldReceive('resetUserPassword')
        ->once()
        ->andReturn(true);

    $response = $this->actingAs($this->admin)
        ->json('PUT', "/api/v1/users/{$targetUser->id}/keycloak/reset_password");

    $response->assertStatus(200);
});

it('returns 404 when resetting password for a non-existent user id', function () {
    $nonExistentId = User::max('id') + 9999;

    $response = $this->actingAs($this->admin)
        ->json('PUT', "/api/v1/users/{$nonExistentId}/keycloak/reset_password");

    // Should 404 gracefully — not throw a null-pointer exception
    $response->assertStatus(404);
});

// ---------------------------------------------------------------------------
// Vuln 7: resendKeycloakVerificationEmailById — authorization checks
// ---------------------------------------------------------------------------

it('blocks a regular user from triggering a verification email for another address', function () {
    $response = $this->actingAs($this->user)
        ->json('PUT', '/api/v1/users/keycloak/resend_verify_email', [
            'email' => 'someone.else@example.com',
        ]);

    $response->assertStatus(403);
});

it('blocks an organisation admin from triggering verification for an unrelated email', function () {
    $response = $this->actingAs($this->organisation_admin)
        ->json('PUT', '/api/v1/users/keycloak/resend_verify_email', [
            'email' => 'unrelated@example.com',
        ]);

    $response->assertStatus(403);
});

it('allows a user to request verification resend for their own email address', function () {
    // The gate passes, so the response will not be 403 regardless of downstream
    // Keycloak state (e.g. already claimed / verified — different error codes).
    $response = $this->actingAs($this->user)
        ->json('PUT', '/api/v1/users/keycloak/resend_verify_email', [
            'email' => $this->user->email,
        ]);

    expect($response->status())->not->toBe(403);
});

it('allows an admin to request verification resend for any email address', function () {
    $targetUser = User::where('user_group', User::GROUP_USERS)->first();

    Http::fake([
        '*/realms/*/protocol/openid-connect/token' => Http::response(
            ['access_token' => 'fake-admin-token'],
            200
        ),
    ]);

    \Keycloak::shouldReceive('searchUserByEmail')
        ->andReturn([['id' => 'some-keycloak-uuid', 'emailVerified' => false]]);

    \Keycloak::shouldReceive('resendVerifyEmail')
        ->once()
        ->andReturn(true);

    $response = $this->actingAs($this->admin)
        ->json('PUT', '/api/v1/users/keycloak/resend_verify_email', [
            'email' => $targetUser->email,
        ]);

    expect($response->status())->not->toBe(403);
});

it('does not expose whether an email belongs to a different user via a 403', function () {
    // Previously any user could probe arbitrary emails and receive distinct error
    // messages that confirmed registration status. After the fix, cross-user
    // attempts are short-circuited with a uniform 403 before any DB/Keycloak
    // lookup occurs.
    $probeEmails = [
        'probe1@example.com',
        'probe2@example.com',
        $this->admin->email,
    ];

    foreach ($probeEmails as $email) {
        if (strtolower($email) === strtolower($this->user->email)) {
            continue;
        }

        $response = $this->actingAs($this->user)
            ->json('PUT', '/api/v1/users/keycloak/resend_verify_email', [
                'email' => $email,
            ]);

        $response->assertStatus(403);
    }
});
