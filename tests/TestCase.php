<?php

namespace Tests;

use Keycloak;
use App\Models\User;
use Tests\Traits\RefreshDatabaseLite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabaseLite;
    protected $user = null;
    protected $custodian_admin = null;
    protected $organisation_admin = null;
    protected $organisation_delegate = null;

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

    }

    protected function withUsers(): void
    {
        $this->user = User::where('user_group', User::GROUP_USERS)->first();
        $this->custodian_admin = User::where('user_group', User::GROUP_CUSTODIANS)->first();
        $this->custodian_admin->update([
            'keycloak_id' => (string) Str::uuid(),
            'unclaimed' => 0,
        ]);
        $this->organisation_admin = User::where('user_group', User::GROUP_ORGANISATIONS)->where("is_delegate", 0)->first();
        $this->organisation_delegate = User::where('user_group', User::GROUP_ORGANISATIONS)->where("is_delegate", 1)->first();
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

}
