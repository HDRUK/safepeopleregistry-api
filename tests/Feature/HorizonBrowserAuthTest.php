<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Mockery;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Browser access to the Horizon dashboard.
 *
 * Browsers can't send a Keycloak Bearer header, so Horizon is protected by a
 * server-side session (web guard) created via a Keycloak authorization-code
 * login. See docs/ARCHITECTURE/HORIZON_DASHBOARD_AUTH.md.
 */
class HorizonBrowserAuthTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
        $this->enableMiddleware();
    }

    public static function environments(): array
    {
        return [['prod'], ['preprod'], ['dev'], ['local']];
    }

    private function mockKeycloakProvider(): Mockery\MockInterface
    {
        $provider = Mockery::mock(AbstractProvider::class);
        $provider->shouldReceive('redirectUrl')->with(route('horizon.callback'))->andReturnSelf();
        Socialite::shouldReceive('driver')->with('keycloak')->andReturn($provider);

        return $provider;
    }

    private function mockKeycloakSocialiteUser(string $keycloakId): void
    {
        $socialiteUser = (new SocialiteUser())->map(['id' => $keycloakId]);
        $this->mockKeycloakProvider()->shouldReceive('user')->andReturn($socialiteUser);
    }

    // Regression: a browser session must be able to reach the dashboard

    public function test_a_browser_with_an_authenticated_web_session_can_open_the_dashboard(): void
    {
        $this->actingAs($this->admin, 'web')->get('/horizon')->assertOk();
    }

    public function test_an_unauthenticated_browser_is_redirected_to_the_horizon_login_not_the_portal(): void
    {
        $this->get('/horizon')->assertRedirect(route('horizon.login'));
    }

    // Login flow

    public function test_the_login_route_redirects_to_keycloak(): void
    {
        $this->mockKeycloakProvider()
            ->shouldReceive('redirect')
            ->andReturn(redirect()->away('https://keycloak.example.com/auth'));

        $this->get(route('horizon.login'))
            ->assertRedirect('https://keycloak.example.com/auth');
    }

    public function test_a_known_admin_is_logged_into_the_web_guard_and_returned_to_horizon(): void
    {
        $this->admin->update(['keycloak_id' => (string) Str::uuid()]);
        $this->mockKeycloakSocialiteUser($this->admin->keycloak_id);

        $this->get(route('horizon.callback'))
            ->assertRedirect('/' . config('horizon.path'));

        $this->assertSame($this->admin->id, Auth::guard('web')->id());
    }

    public function test_a_known_non_admin_is_refused_without_a_session(): void
    {
        $this->user->update(['keycloak_id' => (string) Str::uuid()]);
        $this->mockKeycloakSocialiteUser($this->user->keycloak_id);

        $this->get(route('horizon.callback'))->assertForbidden();

        $this->assertFalse(Auth::guard('web')->check());
    }

    public function test_a_keycloak_identity_with_no_registry_user_is_refused(): void
    {
        $this->mockKeycloakSocialiteUser((string) Str::uuid());

        $this->get(route('horizon.callback'))->assertForbidden();

        $this->assertFalse(Auth::guard('web')->check());
    }

    // Authorisation: super-admins only, in every environment

    #[DataProvider('environments')]
    public function test_non_admin_web_sessions_are_forbidden_in_every_environment(string $env): void
    {
        config(['app.env' => $env]);

        $nonAdmins = array_filter([$this->user, $this->custodian_admin, $this->organisation_admin]);
        $this->assertNotEmpty($nonAdmins);

        foreach ($nonAdmins as $nonAdmin) {
            Auth::guard('web')->logout();

            $this->actingAs($nonAdmin, 'web')->get('/horizon')->assertForbidden();
        }
    }

    #[DataProvider('environments')]
    public function test_admin_web_sessions_are_allowed_in_every_environment(string $env): void
    {
        config(['app.env' => $env]);

        $this->assertSame(User::GROUP_ADMINS, $this->admin->user_group);

        $this->actingAs($this->admin, 'web')->get('/horizon')->assertOk();
    }
}
