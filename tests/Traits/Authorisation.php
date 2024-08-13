<?php

namespace Tests\Traits;

use Carbon\Carbon;


use Illuminate\Support\Facades\Http;

trait Authorisation
{
    public function getMockedKeycloakPayload(): array
    {
        $now = Carbon::now()->addMinutes(30);

        $tokenPayload = [
            'aud' => 'account',
            'exp' => $now->toArray()['timestamp'],
            'iss' => env('KEYCLOAK_BASE_URL') . ':8443/realms/SPeeDI-Registry',
        ];

        return $tokenPayload;
    }

    public function getAuthToken(): mixed
    {
        $authUrl = env('KEYCLOAK_BASE_URL').'/realms/'.env('KEYCLOAK_REALM').'/protocol/openid-connect/token';

        $credentials = [
            'username' => env('KEYCLOAK_TEST_USERNAME'),
            'password' => env('KEYCLOAK_TEST_PASSWORD'),
            'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
            'client_id' => env('KEYCLOAK_CLIENT_ID'),
            'grant_type' => 'password',
        ];

        $response = Http::asForm()->post($authUrl, $credentials);
        $responseData = $response->json();

        return $responseData['access_token'];
    }
}
