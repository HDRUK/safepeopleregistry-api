<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Middleware\LogRequestResponse;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The global request/response logger must never write credentials to logs.
 * See docs/ARCHITECTURE/REQUEST_RESPONSE_LOGGING.md.
 */
class LogRequestResponseTest extends TestCase
{
    private function runMiddleware(Request $request, ?Response $response = null): void
    {
        $response ??= new Response('ok', 200);

        (new LogRequestResponse())->handle($request, fn () => $response);
    }

    private function loggedContext(): array
    {
        $context = null;
        Log::shouldHaveReceived('info')
            ->withArgs(function ($message, $ctx) use (&$context) {
                if ($message !== 'LogRequestResponse') {
                    return false;
                }
                $context = $ctx;
                return true;
            })
            ->once();

        return $context;
    }

    // Regression: session credentials must not reach the logs

    public function test_request_cookie_header_is_masked(): void
    {
        Log::spy();
        $request = Request::create('/api/v1/users', 'GET', [], [], [], [
            'HTTP_COOKIE' => 'laravel_session=super-secret-session-id',
        ]);

        $this->runMiddleware($request);

        $context = $this->loggedContext();
        $this->assertSame('***', $context['request']['headers']['cookie']);
        $this->assertStringNotContainsString('super-secret-session-id', json_encode($context));
    }

    public function test_response_set_cookie_header_is_masked(): void
    {
        Log::spy();
        $response = new Response('ok', 200);
        $response->headers->set('Set-Cookie', 'laravel_session=super-secret-session-id; HttpOnly');

        $this->runMiddleware(Request::create('/api/v1/users', 'GET'), $response);

        $context = $this->loggedContext();
        $this->assertSame('***', $context['response']['headers']['set-cookie']);
        $this->assertStringNotContainsString('super-secret-session-id', json_encode($context));
    }

    public function test_custodian_api_key_in_a_response_body_is_masked(): void
    {
        Log::spy();
        $response = new Response(json_encode([
            'message' => 'success',
            'data' => ['id' => 1, 'name' => 'Example Custodian', 'unique_identifier' => 'secret-api-key-value'],
        ]), 200);

        $this->runMiddleware(Request::create('/api/v1/custodians/1', 'GET'), $response);

        $context = $this->loggedContext();
        $this->assertStringNotContainsString('secret-api-key-value', json_encode($context));
        $this->assertStringContainsString('Example Custodian', $context['response']['body']);
    }

    public static function unloggedPaths(): array
    {
        return [
            'oauth callback with code' => ['/auth/horizon/callback?code=secret-auth-code&state=secret-state'],
            'horizon login' => ['/auth/horizon/login'],
            'horizon dashboard' => ['/horizon'],
            'horizon api (job payloads)' => ['/horizon/api/jobs/recent'],
        ];
    }

    #[DataProvider('unloggedPaths')]
    public function test_horizon_and_horizon_auth_requests_are_not_logged(string $uri): void
    {
        Log::spy();

        $this->runMiddleware(Request::create($uri, 'GET'));

        Log::shouldNotHaveReceived('info');
    }

    public function test_custodian_secret_rotation_requests_are_not_logged(): void
    {
        Log::spy();

        $this->runMiddleware(Request::create('/api/v1/custodians/1/credentials/rotate', 'POST'));

        Log::shouldNotHaveReceived('info');
    }

    public function test_custodian_export_requests_are_not_logged(): void
    {
        Log::spy();

        $this->runMiddleware(Request::create('/api/v1/custodian/export/users?page=1', 'GET'));

        Log::shouldNotHaveReceived('info');
    }

    // Happy path: ordinary API traffic is still logged

    public function test_ordinary_requests_are_still_logged_with_non_sensitive_detail(): void
    {
        Log::spy();
        $request = Request::create('/api/v1/organisations?code=GB', 'POST', ['name' => 'Example Org', 'password' => 'hunter2']);

        $this->runMiddleware($request, new Response('created', 201));

        $context = $this->loggedContext();
        $this->assertSame('POST', $context['request']['method']);
        $this->assertStringContainsString('/api/v1/organisations?code=GB', $context['request']['url']);
        $this->assertStringContainsString('Example Org', $context['request']['body']);
        $this->assertStringNotContainsString('hunter2', $context['request']['body']);
        $this->assertSame(201, $context['response']['status']);
    }

    public function test_paths_that_merely_start_with_horizon_text_are_still_logged(): void
    {
        Log::spy();

        $this->runMiddleware(Request::create('/api/v1/horizons', 'GET'));

        $this->loggedContext();
    }
}
