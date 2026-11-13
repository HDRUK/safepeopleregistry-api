<?php

namespace Tests\Unit;

use App\Models\User;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Which HubSpot properties a user owns on their shared contact.
 *
 * Ownership is only asked for while sync is enabled, so an environment with
 * no HubSpot property is a misconfigured deployment and must fail loudly.
 */
class UserHubspotOwnershipTest extends TestCase
{
    public static function nonProductionEnvironments(): array
    {
        return [
            'development' => ['development', 'safe_people_registry_development'],
            'dev' => ['dev', 'safe_people_registry_development'],
            'preprod' => ['preprod', 'safe_people_registry_preproduction'],
            'preproduction' => ['preproduction', 'safe_people_registry_preproduction'],
        ];
    }

    public static function productionEnvironments(): array
    {
        return [
            'prod' => ['prod'],
            'production' => ['production'],
        ];
    }

    public function test_an_environment_with_no_hubspot_property_fails_naming_it(): void
    {
        $this->app['env'] = 'staging';

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("'staging'");

        (new User())->hubspotOwnership();
    }

    #[DataProvider('nonProductionEnvironments')]
    public function test_outside_production_a_user_owns_only_their_environment_flag(string $env, string $property): void
    {
        $this->app['env'] = $env;

        $ownership = (new User())->hubspotOwnership();

        $this->assertSame([$property], $ownership->ownedProperties);
    }

    #[DataProvider('productionEnvironments')]
    public function test_in_production_a_user_owns_their_environment_flag_and_persona(string $env): void
    {
        $this->app['env'] = $env;

        $ownership = (new User())->hubspotOwnership();

        $this->assertSame(
            ['safe_people_registry_production', 'safe_people_registry__prod_persona'],
            $ownership->ownedProperties,
        );
    }
}
