<?php

namespace App\Keycloak;

use Http;
use Hash;
use Exception;
use App\Models\User;
use App\Models\Registry;
use App\Models\IssuerUser;
use App\Models\RegistryHasOrganisation;
use Illuminate\Support\Str;

class Keycloak
{
    public const USERS_URL = '/users';

    public function create(array $credentials): array
    {
        try {
            $isResearcher = isset($credentials['is_researcher']) ? true : false;
            $isIssuer = isset($credentials['is_issuer']) ? true : false;
            $isOrganisation = isset($credentials['is_organisation']) ? true : false;

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

            ($isResearcher) ? $payload['groups'][] = '/Researchers' : null;
            ($isIssuer) ? $payload['groups'][] = '/Issuers' : null;
            ($isOrganisation) ? $payload['groups'][] = '/Organisations' : null;
            $userGroup = $this->determineUserGroup($credentials);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->getServiceToken(),
            ])->post(
                $this->makeUrl(self::USERS_URL),
                $payload
            );

            $content = json_decode($response->body(), true);

            if ($response->status() === 201) {
                $headers = $response->headers();
                $parts = explode('/', $headers['Location'][0]);
                $last = count($parts) - 1;
                $newUserId = $parts[$last];

                $user = null;

                if ($isResearcher || $isOrganisation) {
                    $user = User::create([
                        'first_name' => $credentials['first_name'],
                        'last_name' => $credentials['last_name'],
                        'email' => $credentials['email'],
                        'consent_scrape' => isset($credentials['consent_scrape']) ? $credentials['consent_scrape'] : 0,
                        'provider' => 'keycloak',
                        'provider_sub' => '',
                        'keycloak_id' => $newUserId,
                        'user_group' => $userGroup,
                    ]);
                } elseif ($isIssuer) {
                    $user = IssuerUser::create([
                        'first_name' => $credentials['first_name'],
                        'last_name' => $credentials['last_name'],
                        'email' => $credentials['email'],
                        'provider' => 'keycloak',
                        'provider_sub' => '',
                        'keycloak_id' => $newUserId,
                        'issuer_id' => $credentials['issuer_id'],
                    ]);
                }

                if (!$user) {
                    return [
                        'success' => false,
                        'error' => 'unable to create user',
                    ];
                }

                if ($userGroup === 'RESEARCHERS') {
                    $signature = Str::random(64);
                    $digiIdent = Hash::make(
                        $signature.
                        ':'.env('REGISTRY_SALT_1').
                        ':'.env('REGISTRY_SALT_2')
                    );

                    $registry = Registry::create([
                        'dl_ident' => '',
                        'pp_ident' => '',
                        'digi_ident' => $digiIdent,
                        'verified' => 0,
                    ]);

                    $user->update([
                        'registry_id' => $registry->id,
                    ]);

                    if ($credentials['organisation_id'] !== null) {
                        RegistryHasOrganisation::create([
                            'registry_id' => $registry->id,
                            'organisation_id' => $credentials['organisation_id'],
                        ]);
                    }
                }

                return [
                    'success' => true,
                    'error' => null,
                ];
            }

            if (isset($content['errorMessage'])) {
                return [
                    'success' => false,
                    'error' => $content['errorMessage'],
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'unknown error',
                ];
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function login(string $username, string $password): array
    {
        try {
            $authUrl = env('KEYCLOAK_BASE_URL').'/realms/'.env('KEYCLOAK_REALM').'/protocol/openid-connect/token';

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

            return [
                'user' => null,
                'response' => null,
                'status' => 401,
            ];

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function changePassword(string $keycloakUserId, string $password): mixed
    {
        try {
            $authUrl = env('KEYCLOAK_BASE_URL') . '/admin/realms/' . env('KEYCLOAK_REALM') . '/users/' . $keycloakUserId . '/reset-password';

            $payload = [
                'type' => 'password',
                'temporary' => 'false',
                'value' => $password,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getServiceToken(),
            ])->put($authUrl, $payload);

            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function logout(string $token): bool
    {
        try {
            $authUrl = env('KEYCLOAK_BASE_URL').'/realms/'.env('KEYCLOAK_REALM').'/protocol/openid-connect/logout';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
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
            $authUrl = env('KEYCLOAK_BASE_URL').'/realms/'.env('KEYCLOAK_REALM').'/users/'.$id;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
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
            $authUrl = env('KEYCLOAK_BASE_URL').'/realms/'.env('KEYCLOAK_REALM').'/protocol/openid-connect/token';

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
        return env('KEYCLOAK_BASE_URL').'/admin/realms/'.env('KEYCLOAK_REALM').$path;
    }

    public function determineUserGroup(array $input): string
    {
        if (isset($input['is_researcher']) && $input['is_researcher']) {
            return 'RESEARCHERS';
        }

        if (isset($input['is_issuer']) && $input['is_issuer']) {
            return 'ISSUERS';
        }

        if (isset($input['is_organisation']) && $input['is_organisation']) {
            return 'ORGANISATIONS';
        }

        return '';
    }
}
