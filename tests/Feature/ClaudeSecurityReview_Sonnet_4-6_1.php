<?php

/**
 * Security regression tests — Route Authentication
 *
 * Covers:
 *   Vuln 1 & 6 — Horizon JWT forgery / token-in-URL (Authenticate.php)
 *   Vuln 3     — Unauthenticated ONS researcher feed (routes/api.php)
 *   Vuln 4     — Environment-conditional auth bypass (Authenticate.php)
 */

namespace Tests\Feature;

use App\Http\Middleware\Authenticate;
use Illuminate\Http\UploadedFile;
use KeycloakGuard\ActingAsKeycloakUser;
use Tests\Traits\Authorisation;

uses(ActingAsKeycloakUser::class);
uses(Authorisation::class);

beforeEach(function () {
    $this->withUsers();
});

// ---------------------------------------------------------------------------
// Vuln 3: ONS researcher feed must require authentication
// ---------------------------------------------------------------------------

it('rejects an unauthenticated POST to the ONS researcher feed with 401', function () {
    $this->enableMiddleware();

    $response = $this->json('POST', '/api/v1/ons_researcher_feed', [
        'file' => UploadedFile::fake()->create('feed.csv', 10, 'text/csv'),
    ]);

    $response->assertStatus(401);
});

it('accepts an authenticated POST to the ONS researcher feed', function () {
    $response = $this->actingAsKeycloakUser($this->admin, $this->getMockedKeycloakPayload())
        ->json('POST', '/api/v1/ons_researcher_feed', [
            'file' => UploadedFile::fake()->create('feed.csv', 10, 'text/csv'),
        ]);

    $response->assertStatus(200);
});

// ---------------------------------------------------------------------------
// Vuln 4: Auth must be enforced regardless of APP_ENV value
// ---------------------------------------------------------------------------

it('enforces authentication on user listing regardless of APP_ENV', function () {
    $this->enableMiddleware();

    $response = $this->json('GET', '/api/v1/users');

    $response->assertStatus(401);
});

it('enforces authentication on custodian listing regardless of APP_ENV', function () {
    $this->enableMiddleware();

    $response = $this->json('GET', '/api/v1/custodians');

    $response->assertStatus(401);
});

it('enforces authentication on write endpoints regardless of APP_ENV', function () {
    $this->enableMiddleware();

    $response = $this->json('POST', '/api/v1/projects');

    $response->assertStatus(401);
});

// ---------------------------------------------------------------------------
// Vuln 1 & 6: Horizon must not accept a crafted JWT via query parameter
// ---------------------------------------------------------------------------

it('does not grant Horizon access via a forged JWT in the query string', function () {
    $this->enableMiddleware();

    // Craft a structurally valid JWT with an arbitrary sub — signature is fake
    $header  = rtrim(strtr(base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])), '+/', '-_'), '=');
    $payload = rtrim(strtr(base64_encode(json_encode(['sub' => 'arbitrary-keycloak-id', 'exp' => time() + 3600])), '+/', '-_'), '=');
    $forgedToken = $header . '.' . $payload . '.invalidsignature';

    $response = $this->get('/horizon?token=' . $forgedToken);

    // Must redirect away — not grant a 200
    expect($response->status())->not->toBe(200);
    $response->assertRedirect();
});

it('redirects unauthenticated requests to the Horizon dashboard', function () {
    $this->enableMiddleware();

    $response = $this->get('/horizon');

    expect($response->status())->not->toBe(200);
    $response->assertRedirect();
});

// ---------------------------------------------------------------------------
// Vuln 1: Authenticate middleware must not expose a getUserFromToken method
// ---------------------------------------------------------------------------

it('removes the getUserFromToken method that decoded JWTs without signature verification', function () {
    expect(method_exists(Authenticate::class, 'getUserFromToken'))->toBeFalse();
});

it('removes the handleHorizonAuth session-based token path', function () {
    $source = file_get_contents((new \ReflectionClass(Authenticate::class))->getFileName());

    expect($source)
        ->not->toContain("query('token')")
        ->not->toContain('horizon_authenticated')
        ->not->toContain('getUserFromToken');
});
