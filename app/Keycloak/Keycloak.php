<?php

namespace App\Keycloak;

use Http;
use Exception;

use App\Models\User;

use Illuminate\Http\JsonResponse;

class Keycloak {
    const USERS_URL = '/users';

    public function createUser(array $credentials): bool
    {
        try {
            $payload = [
                'username' => $credentials['email'],
                'email' => $credentials['email'],
                'emailVerified' => false,
                'enabled' => true,
                'firstName' => $credentials['first_name'],
                'lastName' => $credentials['last_name'],
                'credentials' => [
                    [
                        'value' => $credentials['password'],
                        'temporary' => false,
                        'type' => 'password',
                    ],
                ],
                'requiredActions' => [
                    'VERIFY_EMAIL',
                    'CONFIGURE_TOTP',
                ],
            ];

            isset($credentials['is_researcher']) ? $payload['groups'][] = '/Researchers' : null;
            isset($credentials['is_issuer']) ? $payload['groups'][] = '/Issuers' : null;
            isset($credentials['is_organisation']) ? $payload['groups'][] = '/Organisations' : null;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getServiceToken(),
            ])->post(
                $this->makeUrl(self::USERS_URL),
                $payload
            );

            if ($response->status() === 201) {
                $headers = $response->headers();
                $parts = explode('/', $headers['Location'][0]);
                $last = count($parts) -1;
                $newUserId = $parts[$last];

                $user = User::create([
                    'name' => $credentials['first_name'] . ' ' . $credentials['last_name'],
                    'email' => $credentials['email'],
                    'provider' => 'keycloak',
                    'provider_sub' => '',
                    'keycloak_id' => $newUserId,
                ]);

                if (!$user)  return false; 

                if (isset($credentials['is_researcher']) && $credentials['is_researcher'] === true) {
                    $registry = Registry::create([
                        'user_id' => $user->id,
                    ]);
                }

                return true;
            }

            return false;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function login(string $username, string $password): array
    {
        try {
            $authUrl = env('KEYCLOAK_BASE_URL') . '/realms/' . env('KEYCLOAK_REALM') . '/protocol/openid-connect/token';

            $credentials = [
                'username' => $username,
                'password' => $password,
                'client_id' => env('KEYCLOAK_CLIENT_ID'),
                'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
                'grant_type' => 'password',
            ];

            $response = Http::asForm()->post($authUrl, $credentials);
            $responseData = $response->json();

            if ($response->status() === 200) {
                $user = User::where('email', $username)->first();
                if ($user) {
                    return [
                        'user' => $user,
                        'response' => $responseData,
                        'status' => $response->status(),
                    ];
                }
            }

            return response()->json([
                'response' => 'unauthorised',
                'status' => 401,
            ], 401);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function logout(string $token): bool
    {
        try {
            $authUrl = env('KEYCLOAK_BASE_URL') . '/realms/' . env('KEYCLOAK_REALM') . '/protocol/openid-connect/logout';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->post($authUrl);

            if ($response->status() === 200) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function me(string $token, string $id): mixed
    {
        try {
            $authUrl = env('KEYCLOAK_BASE_URL') . '/realms/' . env('KEYCLOAK_REALM') . '/users/' . $id;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->post($authUrl);

            $responseData = $response->json();
            dd($responseData);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function getServiceToken(): string
    {
        try {
            $authUrl = env('KEYCLOAK_BASE_URL') . '/realms/' . env('KEYCLOAK_REALM') . '/protocol/openid-connect/token';

            $credentials = [
                'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
                'client_id' => env('KEYCLOAK_CLIENT_ID'),
                'grant_type' => 'client_credentials',
            ];

            $response = Http::asForm()->post($authUrl, $credentials);
            $responseData = $response->json();

            return $responseData['access_token'];

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    private function makeUrl(string $path): string
    {
        return env('KEYCLOAK_BASE_URL') . '/admin/realms/' . env('KEYCLOAK_REALM') . $path;
    }
}
