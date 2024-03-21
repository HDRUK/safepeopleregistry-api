<?php

namespace Tests\Traits;

use Hash;
use Config;

use App\Models\User;

use App\Http\Controller\AuthController;

trait Authorisation
{
    const USER_EMAIL = 'constants.test.user.email';
    const USER_PASSWORD = 'constants.test.user.password';

    const AUTH_URL = '/api/v1/auth/login';

    public function getAuthToken(): mixed
    {
        $credentials = [
            'email' => Config::get(self::USER_EMAIL),
            'password' => Config::get(self::USER_PASSWORD),
        ];

        $response = $this->json(
            'POST',
            self::AUTH_URL,
            $credentials,
            [
                'Accept' => 'application/json',
            ]
        );
        return $response['token'];
    }
}