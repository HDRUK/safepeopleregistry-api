<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

use App\Models\RegistryHasTraining;
use Database\Factories\CustodianFactory;
use Tests\Traits\Authorisation;
use KeycloakGuard\ActingAsKeycloakUser;

use Database\Factories\UserFactory;
use Illuminate\Support\Facades\DB;

class PermissionMatrixTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1';
    protected $admin;
    protected $user2;
    protected $custodian2;
    protected $organisation2;
    protected $users;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withMiddleware();
        $this->withUsers();
        $this->admin = User::factory()->create(['user_group' => User::GROUP_ADMINS]);
        $this->user2 = User::factory()->create(['user_group' => User::GROUP_USERS]);
        $this->custodian2 = User::factory()->create(['user_group' => User::GROUP_CUSTODIANS]);
        $this->organisation2 = User::factory()->create(['user_group' => User::GROUP_ORGANISATIONS]);

        $this->users = [
            'admin' => $this->admin,
            'custodian1' => $this->custodian_admin,
            'custodian2' => $this->custodian2,
            'organisation1' => $this->organisation_admin,
            'organisation2' => $this->organisation2,
            'delegate' => $this->organisation_delegate,
            'researcher1' => $this->user,
            'researcher2' => $this->user2,
        ];
    }

    public function test_main_permissions_matrix()
    {
        $expectedMatrix = [
            [
                'method' => 'get',
                'route' => '/organisations/1',
                'permissions' => [
                    'admin' => 200,
                    'custodian1' => 200,
                    'custodian2' => 200,
                    'organisation1' => 200,
                    'organisation2' => 403,
                    'delegate' => 200,
                    'researcher1' => 403,
                    'researcher2' => 403,
                ],
            ],
            [
                'method' => 'get',
                'route' => '/organisations/2',
                'permissions' => [
                    'admin' => 200,
                    'custodian1' => 200,
                    'custodian2' => 200,
                    'organisation1' => 403,
                    'organisation2' => 403,
                    'delegate' => 403,
                    'researcher1' => 403,
                    'researcher2' => 403,
                ],
            ],
            [
                'method' => 'get',
                'route' => '/custodians/1',
                'permissions' => [
                    'admin' => 200,
                    'custodian1' => 200,
                    'custodian2' => 403,
                    'organisation1' => 403,
                    'organisation2' => 403,
                    'delegate' => 403,
                    'researcher1' => 403,
                    'researcher2' => 403,
                ],
            ],
            [
                'method' => 'get',
                'route' => '/custodians/2',
                'permissions' => [
                    'admin' => 200,
                    'custodian1' => 403,
                    'custodian2' => 403,
                    'organisation1' => 403,
                    'organisation2' => 403,
                    'delegate' => 403,
                    'researcher1' => 403,
                    'researcher2' => 403,
                ],
            ],
            [
                'method' => 'get',
                'route' => '/users/' . $this->user->id,
                'permissions' => [
                    'admin' => 200,
                    'custodian1' => 200,
                    'custodian2' => 200,
                    'organisation1' => 200,
                    'organisation2' => 200,
                    'delegate' => 200,
                    'researcher1' => 200,
                    'researcher2' => 403,
                ],
            ],
            [
                'method' => 'get',
                'route' => '/users/' . $this->user2->id,
                'permissions' => [
                    'admin' => 200,
                    'custodian1' => 200,
                    'custodian2' => 200,
                    'organisation1' => 200,
                    'organisation2' => 200,
                    'delegate' => 200,
                    'researcher1' => 403,
                    'researcher2' => 200,
                ],
            ],
        ];

        $this->runTests($expectedMatrix);
    }

    public function test_user_update_permissions_matrix()
    {
        $definition = UserFactory::new()->definition();

        $expectedMatrix = [
            [
                'method' => 'post',
                'route' => '/users',
                'payload' => $definition,
                'permissions' => [
                    'admin' => 201,
                    'custodian1' => 403,
                    'custodian2' => 403,
                    'organisation1' => 403,
                    'organisation2' => 403,
                    'delegate' => 403,
                    'researcher1' => 403,
                    'researcher2' => 403,
                ],
            ],
            [
                'method' => 'put',
                'route' => '/users/' . $this->user->id,
                'payload' => [
                    'first_name'  => fake()->firstname()
                ],
                'permissions' => [
                    'admin' => 200,
                    'custodian1' => 200,
                    'custodian2' => 200,
                    'organisation1' => 200,
                    'organisation2' => 200,
                    'delegate' => 200,
                    'researcher1' => 200,
                    'researcher2' => 403,
                ],
            ],
        ];
        $this->runTests($expectedMatrix);
    }

    public function test_training_permissions_matrix()
    {
        $userTrainingId = RegistryHasTraining::where('registry_id', $this->user->registry_id)
            ->first()->training_id;
        $expectedMatrix = [
            [
                'method' => 'get',
                'route' => '/training/1',
                'permissions' => [
                    'admin' => 200,
                    'custodian1' => 200,
                    'custodian2' => 200,
                    'organisation1' => 200,
                    'organisation2' => 200,
                    'delegate' => 200,
                    'researcher1' => 200,
                    'researcher2' => 200,
                ],
            ],
            [
                'method' => 'get',
                'route' => '/training/registry/' . $this->user->registry_id,
                'permissions' => [
                    'admin' => 200,
                    'custodian1' => 200,
                    'custodian2' => 200,
                    'organisation1' => 200,
                    'organisation2' => 200,
                    'delegate' => 200,
                    'researcher1' => 200,
                    'researcher2' => 403,
                ],
            ],
            [ # currently anyone can create a training
                'method' => 'post',
                'route' => '/training',
                'payload' => [
                    'registry_id' => $this->user->registry_id,
                    'provider' => fake()->name(),
                    'awarded_at' => fake()->dateTime()->format('Y-m-d H:i:s'),
                    'expires_at' => fake()->dateTime()->format('Y-m-d H:i:s'),
                    'expires_in_years' => fake()->numberBetween(1, 5),
                    'training_name' => fake()->name(),
                    'certification_id' => null,
                    'pro_registration' => fake()->randomElement([0, 1]),
                ],
                'permissions' => [
                    'admin' => 201,
                    'custodian1' => 201,
                    'custodian2' => 201,
                    'organisation1' => 201,
                    'organisation2' => 201,
                    'delegate' => 201,
                    'researcher1' => 201,
                    'researcher2' => 201,
                ],
            ],
            [
                'method' => 'put',
                'route' => '/training/' . $userTrainingId,
                'permissions' => [
                    'admin' => 200,
                    'custodian1' => 200,
                    'custodian2' => 200,
                    'organisation1' => 200,
                    'organisation2' => 200,
                    'delegate' => 200,
                    'researcher1' => 200, # should be 200 if created it or not?
                    'researcher2' => 200, # should be 403?
                ],
            ],
        ];

        $this->runTests($expectedMatrix);
    }

    public function test_custodian_permissions_matrix()
    {
        $definition = CustodianFactory::new()->definition();
        foreach ($definition as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $definition[$key] = $value->format('Y-m-d H:i:s');
            }
        }
        $expectedMatrix = [
            [
                'method' => 'get',
                'route' => '/custodians',
                'permissions' => [
                    'admin' => 200,
                    'custodian1' => 200,
                    'custodian2' => 200,
                    'organisation1' => 200,
                    'organisation2' => 200,
                    'delegate' => 200,
                    'researcher1' => 200,
                    'researcher2' => 200,
                ],
            ],
            [
                'method' => 'get',
                'route' => '/custodians/1',
                'permissions' => [
                    'admin' => 200,
                    'custodian1' => 200,
                    'custodian2' => 200,
                    'organisation1' => 200,
                    'organisation2' => 200,
                    'delegate' => 200,
                    'researcher1' => 200,
                    'researcher2' => 200,
                ],
            ],
            [
                'method' => 'get',
                'route' => '/custodians/1/projects',
                'permissions' => [
                    'admin' => 200,
                    'custodian1' => 200,
                    'custodian2' => 200,
                    'organisation1' => 403,
                    'organisation2' => 403,
                    'delegate' => 403,
                    'researcher1' => 403,
                    'researcher2' => 403,
                ],
            ],
            [
                'method' => 'post',
                'route' => '/custodians',
                'payload' => $definition,
                'permissions' => [
                    'admin' => 201,
                    'custodian1' => 403,
                    'custodian2' => 403,
                    'organisation1' => 403,
                    'organisation2' => 403,
                    'delegate' => 403,
                    'researcher1' => 403,
                    'researcher2' => 403,
                ],
            ],
            [
                'method' => 'put',
                'route' => '/custodians/1',
                'payload' => [
                    'name' =>  fake()->company(),
                ],
                'permissions' => [
                    'admin' => 200,
                    'custodian1' => 200,
                    'custodian2' => 403,
                    'organisation1' => 403,
                    'organisation2' => 403,
                    'delegate' => 403,
                    'researcher1' => 403,
                    'researcher2' => 403,
                ],
            ],
            [
                'method' => 'delete',
                'route' => '/custodians/1',
                'permissions' => [
                    'admin' => 200,
                    'custodian1' => 200,
                    'custodian2' => 403,
                    'organisation1' => 403,
                    'organisation2' => 403,
                    'delegate' => 403,
                    'researcher1' => 403,
                    'researcher2' => 403,
                ],
            ],
        ];

        $this->runTests($expectedMatrix);
    }

    private function runTests(array $expectedMatrix)
    {
        $matrix = [];

        foreach ($expectedMatrix as $test) {
            $method = $test['method'] ?? 'get';
            $route = $test['route'];
            $roles = $test['permissions'];
            $payload = $test['payload'] ?? [];

            $routeKey = '[' . strtoupper($method) . '] ' . $route;

            foreach ($roles as $role => $expectedStatus) {
                $user = $this->users[$role];

                DB::beginTransaction();
                //simulate - dont actually make change to DB
                try {
                    $response = $this->actingAs($user)->{$method}(self::TEST_URL . $route, $payload);
                    $status = $response->status();

                    $this->assertEquals(
                        $expectedStatus,
                        $response->getStatusCode(),
                        "[$method] $route: received status {$response->getStatusCode()}, expected {$expectedStatus} → role={$role}"
                    );

                    $matrix[$role][$routeKey] = $status >= 200 && $status < 300 ? "\033[32m$status\033[0m" : "\033[31m$status\033[0m";
                } finally {
                    DB::rollBack(); // Restore DB state after each role test
                }
            }
        }
        $routes = array_map(function ($test) {
            $method = strtoupper($test['method'] ?? 'get');
            return "[$method] " . $test['route'];
        }, $expectedMatrix);

        $this->printMatrixT($matrix, $routes);
    }

    private function runMatrixTests(array $users, array $routes, array $expectedMatrix)
    {
        $matrix = [];

        foreach ($users as $role => $user) {
            foreach ($routes as $index => $route) {
                $expected = $expectedMatrix[$role][$index];

                $response = $this->actingAs($user)->get(self::TEST_URL . $route);
                $status = $response->status();

                $response->assertStatus($expected);

                $matrix[$role][$route] = $status == 200 ? "\033[32m$status\033[0m" : "\033[31m$status\033[0m";
            }
        }

        return $matrix;
    }

    private function printMatrix(array $matrix, array $routes, string $title = 'Permission Matrix')
    {
        $columnWidths = [];

        // Base width for first column ('User')
        $columnWidths['User'] = 25;

        foreach ($routes as $route) {
            $columnWidths[$route] = strlen($route) + 5; // Some margin
        }

        echo "\n$title:\n";

        // Header
        echo str_pad('User', $columnWidths['User']);
        foreach ($routes as $route) {
            echo str_pad($route, $columnWidths[$route]);
        }
        echo "\n";

        foreach ($matrix as $role => $permissions) {
            echo str_pad($role, $columnWidths['User']);
            foreach ($routes as $route) {
                $visibleText = self::stripAnsi($permissions[$route] ?? ''); // Protect if missing
                $padding = $columnWidths[$route] + (strlen($permissions[$route] ?? '') - strlen($visibleText));
                echo str_pad($permissions[$route] ?? '-', $padding);
            }
            echo "\n";
        }
    }

    private function printMatrixT(array $matrix, array $routes)
    {
        $columnWidths = [];

        // Base width for first column ('Route')
        $columnWidths['Route'] = 30;

        $users = [];
        foreach ($matrix as $role => $permissions) {
            $id = $this->users[$role]->id;
            $username = preg_replace('/\d+/', '', $role) . "($id)";
            $users[] = $username;

            $columnWidths[$username] = strlen($username) + 5; // Some margin
        }

        echo str_pad('Route', $columnWidths['Route']);
        foreach ($users as $role) {
            echo str_pad($role, $columnWidths[$role]);
        }
        echo "\n";

        foreach ($routes as $route) {
            echo str_pad($route, $columnWidths['Route']);
            foreach ($matrix as $role => $permissions) {
                $id = $this->users[$role]->id;
                $username = preg_replace('/\d+/', '', $role) . "($id)";
                $visibleText = self::stripAnsi($permissions[$route] ?? '-');
                $padding = $columnWidths[$username] + (strlen($permissions[$route] ?? '-') - strlen($visibleText));
                echo str_pad($permissions[$route] ?? '-', $padding);
            }
            echo "\n";
        }

        echo (str_repeat('_', array_sum($columnWidths))) . "\n";
    }



    private static function stripAnsi($text)
    {
        return preg_replace('/\e\[[0-9;]*m/', '', $text);
    }
}
