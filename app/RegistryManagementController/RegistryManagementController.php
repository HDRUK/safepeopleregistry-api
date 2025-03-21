<?php

namespace App\RegistryManagementController;

use Str;
use Hash;
use Keycloak;
use App\Models\User;
use App\Models\Registry;
use Illuminate\Http\Request;

class RegistryManagementController
{
    public const KC_GROUP_USERS = 'USERS';
    public const KC_GROUP_CUSTODIANS = 'CUSTODIANS';
    public const KC_GROUP_ORGANISATIONS = 'ORGANISATIONS';
    public const KC_GROUP_ADMINS = 'ADMINS';

    /**
     * Creates a Registry ledger within the system
     *
     * @return mixed The registry object created, or FALSE
     */
    public static function createRegistryLedger(): mixed
    {
        return Registry::create([
            'dl_ident' => null,
            'pp_ident' => null,
            'digi_ident' => RegistryManagementController::generateDigitalIdentifierForRegistry(),
            'verified' => 0,
        ]);
    }

    /**
     * Creates a new user based on incoming data.
     *
     * @param array $input The user details to be created from
     * @param Request $request The type of account to create. Being either: user,
     *      organisation or custodian. The key part here is that only "user"'s will
     *      require a Registry ledger created as part of the process. The others are
     *      simply logging in accounts
     * @return mixed
     */
    public static function createNewUser(array $input, Request $request): mixed
    {
        $unclaimedUser = User::where('email', $input['email'])->whereNull('keycloak_id')->first();
        if ($unclaimedUser) {
            $unclaimedUser->first_name = $input['given_name'];
            $unclaimedUser->last_name = $input['family_name'];
            $unclaimedUser->email = $input['email'];
            $unclaimedUser->keycloak_id = $input['sub'];
            $unclaimedUser->unclaimed = 0;
            $unclaimedUser->t_and_c_agreed = 1;
            $unclaimedUser->t_and_c_agreement_date = now();

            $unclaimedUser->save();

            return [
                'unclaimed_user_id' => $unclaimedUser->id
            ];
        }

        $accountType = isset($request['account_type']) ? $request['account_type'] : '';

        switch (strtolower($accountType)) {
            case 'user':
                if (!RegistryManagementController::checkDuplicateKeycloakID($input['sub'])) {
                    $user = User::create([
                        'first_name' => $input['given_name'],
                        'last_name' => $input['family_name'],
                        'email' => $input['email'],
                        'keycloak_id' => $input['sub'],
                        'registry_id' => 0,
                        'user_group' => RegistryManagementController::KC_GROUP_USERS,
                        't_and_c_agreed' => 1,
                        't_and_c_agreement_date' => now(),
                    ]);

                    if ($user) {
                        $user->registry_id = RegistryManagementController::createRegistryLedger()->id;
                        $user->save();
                        Keycloak::updateSoursdDigitalIdentifier($user);
                    }

                    return [
                        'user_id' => $user->id
                    ];
                }

                return false;

            case 'organisation':
                if (!RegistryManagementController::checkDuplicateKeycloakID($input['sub'])) {
                    $user = User::create([
                        'first_name' => $input['given_name'],
                        'last_name' => $input['family_name'],
                        'email' => $input['email'],
                        'keycloak_id' => $input['sub'],
                        'registry_id' => null,
                        'organisation_id' => $request['organisation_id'],
                        'user_group' => RegistryManagementController::KC_GROUP_ORGANISATIONS,
                        't_and_c_agreed' => 1,
                        't_and_c_agreement_date' => now(),
                    ]);

                    return [
                        'user_id' => $user->id
                    ];
                }

                return false;

            case 'custodian':
                if (!RegistryManagementController::checkDuplicateKeycloakID($input['sub'])) {
                    $user = User::create([
                        'first_name' => $input['given_name'],
                        'last_name' => $input['family_name'],
                        'email' => $input['email'],
                        'keycloak_id' => $input['sub'],
                        'registry_id' => null,
                        'user_group' => RegistryManagementController::KC_GROUP_CUSTODIANS,
                        't_and_c_agreed' => 1,
                        't_and_c_agreement_date' => now(),
                    ]);

                    return [
                        'user_id' => $user->id
                    ];
                }

                return false;
        }

        return false;
    }

    /**
     * Checks for the existence of a user already using the assigned
     * keycloak id provided.
     *
     * @param string $id The keycloak_id to check
     * @return boolean
     */
    private static function checkDuplicateKeycloakID(string $id): bool
    {
        $user = User::where('keycloak_id', '=', $id)->first();
        if (!$user) {
            // We don't have a record of this user yet
            return false;
        }

        return true;
    }

    public static function generateDigitalIdentifierForRegistry(): string
    {
        $signature = Str::random(64);
        $digiIdent = Hash::make(
            $signature.
            ':'.env('REGISTRY_SALT_1').
            ':'.env('REGISTRY_SALT_2')
        );

        return $digiIdent;
    }

    public static function createUnclaimedUser(array $user): User
    {
        $registry = Registry::create([
          'dl_ident' => null,
          'pp_ident' => null,
          'digi_ident' => RegistryManagementController::generateDigitalIdentifierForRegistry(),
          'verified' => 0,
        ]);

        return User::create([
            'first_name' => $user['firstname'],
            'last_name' => $user['lastname'],
            'email' => $user['email'],
            'unclaimed' => 1,
            'feed_source' => 'ORG',
            'registry_id' => $registry->id,
            'orc_id' => '',
            'user_group' => isset($user['user_group']) ? $user['user_group'] : '',
            'organisation_id' => isset($user['organisation_id']) ? $user['organisation_id'] : null,
            'custodian_id' => isset($user['custodian_id']) ? $user['custodian_id'] : null,
            'custodian_user_id' => isset($user['custodian_user_id']) ? $user['custodian_user_id'] : null,
            'is_delegate' => isset($user['is_delegate']) ? $user['is_delegate'] : 0,
            'role' => isset($user['role']) ? $user['role'] : null,
        ]);
    }
}
