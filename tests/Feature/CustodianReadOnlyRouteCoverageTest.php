<?php

namespace Tests\Feature;

use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * In each covered controller, every write route a signed-in user can call (non-GET, behind auth:api)
 * must say how CUSTODIAN_READ_ONLY users are treated, so a new method can't be added without deciding.
 *
 * Methods are keyed as ShortControllerName@method. This checks classification only; behaviour is
 * tested in CustodianReadOnlyTest and CustodianReadOnlyGateTest.
 *
 * Adding a write method to a covered controller? Put its Controller@method in one of the lists below.
 * Adding a new controller with write routes? Decide how read-only users are treated, then add it to
 * COVERED_CONTROLLERS.
 */
class CustodianReadOnlyRouteCoverageTest extends TestCase
{
    /**
     * Controllers whose every write method is classified below.
     *
     * @var array<int, string>
     */
    private const COVERED_CONTROLLERS = [
        'ActionLogController',
        'CustodianController',
        'CustodianHasProjectOrganisationController',
        'CustodianHasProjectUserController',
        'CustodianModelConfigController',
        'CustodianUserController',
        'NotificationController',
        'OrganisationController',
        'PermissionController',
        'ProjectController',
        'ProjectDetailController',
        'ProjectHasUserController',
        'TriggerEmailController',
        'UserController',
        'ValidationCheckController',
        'ValidationLogCommentController',
        'ValidationLogController',
    ];

    /**
     * A policy ability or the notReadOnly gate returns 403 for read-only users.
     *
     * @var array<int, string>
     */
    private const REFUSES_READ_ONLY = [
        // CustodianPolicy::update
        'CustodianController@confirmAccessRevoked',
        'CustodianController@destroy',
        'CustodianController@invite',
        'CustodianController@update',
        'CustodianHasProjectOrganisationController@update',
        'CustodianHasProjectUserController@update',
        // CustodianUserPolicy
        'CustodianUserController@destroy',
        'CustodianUserController@invite',
        'CustodianUserController@store',
        'CustodianUserController@update',
        // CustodianModelConfigPolicy
        'CustodianModelConfigController@destroy',
        'CustodianModelConfigController@store',
        'CustodianModelConfigController@update',
        'CustodianModelConfigController@updateDecisionModels',
        // UserPolicy::invite
        'UserController@invite',
        // notReadOnly gate
        'CustodianController@addProject',
        'CustodianController@push',
        'OrganisationController@createOrgWithUser',
        'OrganisationController@custodianInviteUser',
        'OrganisationController@invite',
        'OrganisationController@inviteToContactSuperadmin',
        'OrganisationController@resendInvite',
        'OrganisationController@storeUnclaimed',
        'OrganisationController@storeUnclaimedBeforeSuperadminInvitation',
        'OrganisationController@updateSponsorshipStatuses',
        'PermissionController@assignOrganisationPermissionsToFrom',
        'PermissionController@assignUserPermissionsToFrom',
        'ProjectController@destroy',
        'ProjectController@makePrimaryContact',
        'ProjectController@resendSponsorshipRequest',
        'ProjectController@store',
        'ProjectController@update',
        'ProjectController@updateAllProjectUsers',
        'ProjectController@updateProjectUser',
        'ProjectDetailController@destroy',
        'ProjectDetailController@store',
        'ProjectDetailController@update',
        'ProjectHasUserController@delete',
        'TriggerEmailController@spawnEmail',
        'ValidationCheckController@createCustodianValidationChecks',
        'ValidationCheckController@destroy',
        'ValidationCheckController@store',
        'ValidationCheckController@update',
        'ValidationLogCommentController@destroy',
        'ValidationLogCommentController@store',
        'ValidationLogCommentController@update',
        'ValidationLogController@update',
        'ValidationLogController@updateCustodianValidationLogs',
    ];

    /**
     * An existing check already refuses all custodian users.
     *
     * @var array<int, string>
     */
    private const CUSTODIANS_CANNOT_USE = [
        // admin gate
        'CustodianController@store',
        'OrganisationController@store',
        'OrganisationController@updateApproved',
        'UserController@destroy',
        'UserController@store',
        // OrganisationPolicy: admins or the Organisation's own users only
        'OrganisationController@appointSro',
        'OrganisationController@destroy',
        'OrganisationController@inviteSro',
        'OrganisationController@inviteUser',
        'OrganisationController@removeDelegate',
        'OrganisationController@resendSroInvite',
        'OrganisationController@update',
        // UserPolicy::updateEmailFromInvite: admins or unclaimed Organisation users only
        'UserController@updateUserEmailByInviteCode',
    ];

    /**
     * Deliberately open to read-only users: their own profile, notifications and checklist, and searches.
     *
     * @var array<int, string>
     */
    private const ALLOWED = [
        // Own checklist: ActionLogPolicy refuses read-only users on Custodian action logs
        'ActionLogController@update',
        // Own notifications (UserPolicy::update on themselves)
        'NotificationController@markUserNotificationAsRead',
        'NotificationController@markUserNotificationAsUnread',
        'NotificationController@markUserNotificationsAsRead',
        // Own profile, email and password
        'UserController@resendKeycloakVerificationEmailById',
        'UserController@resetPasswordById',
        'UserController@update',
        'UserController@updateKeycloakUserEmailById',
        // Searches that only read
        'ProjectDetailController@queryGatewayDurByProjectID',
        'UserController@searchUsersByNameAndProfessionalEmail',
    ];

    public function test_every_write_method_in_a_covered_controller_is_classified(): void
    {
        $unclassified = array_diff_key($this->coveredWriteRoutes(), array_flip($this->classified()));

        $this->assertSame([], array_values($unclassified), sprintf(
            "These write routes don't say how read-only custodian users are treated. Add each one to "
            . "REFUSES_READ_ONLY, CUSTODIANS_CANNOT_USE or ALLOWED in %s:\n%s",
            self::class,
            implode("\n", $unclassified)
        ));
    }

    public function test_every_classified_method_is_a_write_route_in_a_covered_controller(): void
    {
        $stale = array_diff($this->classified(), array_keys($this->coveredWriteRoutes()));

        $this->assertSame([], array_values($stale), sprintf(
            'Listed but not a write route in a covered controller; remove it or cover its controller: %s',
            implode(', ', $stale)
        ));
    }

    public function test_every_covered_controller_has_write_routes(): void
    {
        $withWriteRoutes = array_unique(array_map(
            fn (string $action) => explode('@', $action)[0],
            array_keys($this->coveredWriteRoutes())
        ));
        $stale = array_diff(self::COVERED_CONTROLLERS, $withWriteRoutes);

        $this->assertSame([], array_values($stale), 'Covered but has no write routes; remove: ' . implode(', ', $stale));
    }

    public function test_each_method_is_in_one_list_only(): void
    {
        $duplicates = array_keys(array_filter(array_count_values($this->classified()), fn (int $count) => $count > 1));

        $this->assertSame([], $duplicates, 'Listed more than once: ' . implode(', ', $duplicates));
    }

    public function test_short_controller_names_are_unique(): void
    {
        // Keys use the short class name, so two controllers with the same name would be ambiguous
        $byShortName = [];
        foreach (Route::getRoutes() as $route) {
            // Routes inside Route::controller(...)->group() get a leading backslash; it's the same class
            $class = ltrim(explode('@', $route->getActionName())[0], '\\');
            $byShortName[class_basename($class)][$class] = true;
        }

        $ambiguous = array_keys(array_filter($byShortName, fn (array $classes) => count($classes) > 1));

        $this->assertSame([], $ambiguous, 'Controllers share a short name: ' . implode(', ', $ambiguous));
    }

    /**
     * @return array<int, string>
     */
    private function classified(): array
    {
        return [...self::REFUSES_READ_ONLY, ...self::CUSTODIANS_CANNOT_USE, ...self::ALLOWED];
    }

    /**
     * Non-GET routes behind auth:api in covered controllers, keyed by ShortController@method,
     * valued "METHOD uri (ShortController@method)".
     *
     * @return array<string, string>
     */
    private function coveredWriteRoutes(): array
    {
        $routes = [];

        /** @var LaravelRoute $route */
        foreach (Route::getRoutes() as $route) {
            $methods = array_diff($route->methods(), ['GET', 'HEAD']);
            if ($methods === [] || ! in_array('auth:api', $route->gatherMiddleware(), true)) {
                continue;
            }

            [$class, $method] = array_pad(explode('@', $route->getActionName()), 2, '__invoke');
            $controller = class_basename($class);

            if (! in_array($controller, self::COVERED_CONTROLLERS, true)) {
                continue;
            }

            $key = "{$controller}@{$method}";
            $routes[$key] = sprintf('%s %s (%s)', implode('|', $methods), $route->uri(), $key);
        }

        ksort($routes);

        return $routes;
    }
}
