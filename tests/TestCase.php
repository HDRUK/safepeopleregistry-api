<?php

namespace Tests;

use Closure;
use Keycloak;
use App\Models\User;
use App\Models\CustodianUser;
use Tests\Traits\RefreshDatabaseLite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Queue;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabaseLite;

    protected $user = null;
    protected $admin = null;
    protected $custodian_admin = null;
    protected $organisation_admin = null;
    protected $organisation_delegate = null;

    protected bool $shouldFakeQueue = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->liteSetUp();

        $this->disableMiddleware();
        $this->disableObservers();

        Keycloak::shouldReceive('checkUserExists')
            ->andReturn(true);

        Keycloak::shouldReceive('determineUserGroup')
            ->andReturn('USERS');

        if ($this->shouldFakeQueue) {
            Queue::fake();
        }
    }

    protected function withUsers(bool $withCustodianUser = false): void
    {
        $this->user = User::where('user_group', User::GROUP_USERS)->first();

        $this->custodian_admin = User::where('user_group', User::GROUP_CUSTODIANS)->first();

        if (! $this->custodian_admin) {
            $this->custodian_admin = User::factory()->create([
                'user_group' => User::GROUP_CUSTODIANS,
                'email' => 'custodian1@fake.notreal',
            ]);
        }

        $this->custodian_admin->update([
            'keycloak_id' => (string) Str::uuid(),
            'unclaimed' => 0,
        ]);

        if ($withCustodianUser) {
            $custodianUser = CustodianUser::where(
                'email',
                $this->custodian_admin->email
            )->first();

            if (! $custodianUser) {
                $custodianUser = CustodianUser::factory()->create([
                    'email' => $this->custodian_admin->email,
                ]);
            }

            $this->custodian_admin->update([
                'custodian_user_id' => $custodianUser->id,
            ]);
        }

        $this->organisation_admin = User::where('user_group', User::GROUP_ORGANISATIONS)->where("is_delegate", 0)->first();
        $this->organisation_delegate = User::where('user_group', User::GROUP_ORGANISATIONS)->where("is_delegate", 1)->first();

        $this->admin = User::factory()->create(['user_group' => User::GROUP_ADMINS]);
    }

    protected function disableMiddleware(): void
    {
        $this->withoutMiddleware();
    }

    protected function enableMiddleware(): void
    {
        $this->withMiddleware();
    }

    protected function disableObservers()
    {
        Model::unsetEventDispatcher();
    }

    protected function enableObservers()
    {
        Model::setEventDispatcher(app('events'));
    }

    protected function withTemporaryObservers(Closure $callback)
    {
        $this->enableObservers();

        try {
            return $callback();
        } finally {
            $this->disableObservers();
        }
    }
}
