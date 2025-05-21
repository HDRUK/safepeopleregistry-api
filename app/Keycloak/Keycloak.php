<?php

namespace App\Keycloak;

use Http;
use Hash;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Registry;
use App\Models\CustodianUser;
use App\Models\DebugLog;
use RegistryManagementController as RMC;
use Illuminate\Support\Str;

class Keycloak
{
    public const USERS_URL = '/users';
    private static ?string $serviceToken = null;
    private static ?Carbon $tokenCreatedAt = null;
    private static int $tokenExiprationHours = 4;

    public function getUserInfo(string $token)
    {
        $userInfoUrl = env('KEYCLOAK_BASE_URL') . '/realms/' . env('KEYCLOAK_REALM') . '/protocol/openid-connect/userinfo';
        DebugLog::create([
            'class' => Keycloak::class,
            'log' => $userInfoUrl
        ]);
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get($userInfoUrl);
    }

    public function updateSoursdDigitalIdentifier(User $user)
    {
        $userUrl = env('KEYCLOAK_BASE_URL') . '/admin/realms/' . env('KEYCLOAK_REALM') . '/users/' . $user->keycloak_id;

        return Http::withHeaders([
            'Authorization' => $this->getServiceToken(),
        ])->put(
            $userUrl,
            [
                'attributes' => [
                    'soursdDigitalIdentifier' => [
                        Registry::where('id', $user->registry_id)->first()->digi_ident
                    ],
                ],
            ],
        );
    }

    public function create(array $credentials): array
    {
        try {
            $isResearcher = isset($credentials['is_researcher']) ? true : false;
            $isCustodian = isset($credentials['is_custodian']) ? true : false;
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
            ($isCustodian) ? $payload['groups'][] = '/Custodians' : null;
            ($isOrganisation) ? $payload['groups'][] = '/Organisations' : null;
            $userGroup = $this->determineUserGroup($credentials);

            $response = Http::withHeaders([
                'Authorization' => $this->getServiceToken(),
            ])->post(
                $this->makeUrl(self::USERS_URL),
                $payload
            );

            $content = json_decode($response->body(), true);
            $response->close();

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
                } elseif ($isCustodian) {
                    $user = CustodianUser::create([
                        'first_name' => $credentials['first_name'],
                        'last_name' => $credentials['last_name'],
                        'email' => $credentials['email'],
                        'provider' => 'keycloak',
                        'provider_sub' => '',
                        'keycloak_id' => $newUserId,
                        'custodian_id' => $credentials['custodian_id'],
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
                        $signature .
                            ':' . env('REGISTRY_SALT_1') .
                            ':' . env('REGISTRY_SALT_2')
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

                    if (!in_array(env('APP_ENV'), ['testing', 'ci'])) {
                        Keycloak::updateSoursdDigitalIdentifier($user);
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
            $response->close();

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
                'Authorization' => $this->getServiceToken(),
            ])->put($authUrl, $payload);

            return $response;
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
        if (
            self::$serviceToken &&
            self::$tokenCreatedAt &&
            self::$tokenCreatedAt->diffInHours(Carbon::now()) < self::$tokenExiprationHours
        ) {
            return self::$serviceToken;
        }

        try {
            $authUrl = env('KEYCLOAK_BASE_URL') . '/realms/' . env('KEYCLOAK_REALM') . '/protocol/openid-connect/token';

            $credentials = [
                'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
                'client_id' => env('KEYCLOAK_CLIENT_ID'),
                'grant_type' => 'client_credentials',
            ];

            $response = Http::asForm()->post($authUrl, $credentials);
            $responseData = $response->json();
            $response->close();

            self::$serviceToken = 'Bearer ' . $responseData['access_token'];
            self::$tokenCreatedAt = Carbon::now();

            return self::$serviceToken;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function resetServiceToken(): void
    {
        self::$serviceToken = null;
    }

    private function makeUrl(string $path): string
    {
        return env('KEYCLOAK_BASE_URL') . '/admin/realms/' . env('KEYCLOAK_REALM') . $path;
    }

    public function determineUserGroup(array $input): string
    {
        if (isset($input['is_researcher']) && $input['is_researcher']) {
            return RMC::KC_GROUP_USERS;
        }

        if (isset($input['is_custodian']) && $input['is_custodian']) {
            return RMC::KC_GROUP_CUSTODIANS;
        }

        if (isset($input['is_organisation']) && $input['is_organisation']) {
            return RMC::KC_GROUP_ORGANISATIONS;
        }

        return '';
    }

    public function determineUserGroupFromString(string $group): string
    {
        switch (strtolower($group)) {
            case 'users':
                return RMC::KC_GROUP_USERS;
            case 'custodians':
                return RMC::KC_GROUP_CUSTODIANS;
            case 'organisations':
                return RMC::KC_GROUP_ORGANISATIONS;
            default:
                return '';
        }
    }

    public function checkUserExists(int $userId): bool
    {
        if (env('APP_ENV') === 'testing') {
            // When testing, ensure we don't create additional users
            return true;
        }

        try {
            $email = User::where('id', $userId)->first()->email;
            $response = Http::withHeaders([
                'Authorization' => $this->getServiceToken(),
                'Content-Type' => 'application/json',
            ])->get(
                $this->makeUrl(self::USERS_URL),
                [
                    'username' => $email,
                    'exact' => true,
                ]
            );

            return count($response->json()) > 0;
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function createUser(array $credentials): array
    {
        // if (env('APP_ENV') === 'testing') {
        //     return [
        //         'success' => true,
        //         'error' => null,
        //     ];
        // }

        try {
            $payload = [
                'username' => $credentials['email'],
                'email' => $credentials['email'],
                'emailVerified' => false,
                'enabled' => true,
                'firstName' => $credentials['first_name'],
                'lastName' => $credentials['last_name'],
                'credentials' => [],
                'requiredActions' => [
                    'UPDATE_PASSWORD',
                    'VERIFY_EMAIL'
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => $this->getServiceToken(),
                'Content-Type' => 'application/json',
            ])->post(
                $this->makeUrl(self::USERS_URL),
                $payload
            );

            $content = $response->json();
            $response->close();

            if ($response->status() === 201) {
                $headers = array_change_key_case($response->headers(), CASE_LOWER);

                $parts = explode('/', $headers['location'][0]);
                $last = count($parts) - 1;
                $newUserId = $parts[$last];

                $user = User::findOrFail($credentials['id']);
                $user->keycloak_id = $newUserId;
                if ($user->save()) {
                    // Finally send a password-reset email directly from keycloak
                    if ($this->sendKeycloakInvite($newUserId)) {
                        return [
                            'success' => true,
                            'error' => null,
                        ];
                    }
                }

                return [
                    'success' => false,
                    'error' => 'unable to save keycloak_id',
                ];
            }

            return [
                'success' => false,
                'error' => 'unable to create user in keycloak',
            ];
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function sendKeycloakInvite(string $keycloakId): bool
    {
        try {
            $payload = [
                'type' => 'password',
                // Here we set:
                // 16 - length of password
                // true - that it contains letters
                // true - that it contains numbers
                // true - that it contains symbols
                // false - that it contains spaces
                'value' => Str::password(16, true, true, true, false),
                'temporary' => true
            ];

            $response = Http::withHeaders([
                'Authorization' => $this->getServiceToken(),
                'Content-Type' => 'application/json',
            ])->put(
                $this->makeUrl(self::USERS_URL . '/' . $keycloakId . '/reset-password'),
                $payload
            );

            if ($response->status() === 204) {
                $response->close();
                return true;
            }

            $response->close();

            return false;
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
