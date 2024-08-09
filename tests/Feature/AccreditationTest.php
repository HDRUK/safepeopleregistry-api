<?php

namespace Tests\Feature;

use Carbon\Carbon;

use App\Models\User;
use App\Models\Registry;

use Database\Seeders\UserSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

use Tests\Traits\Authorisation;

class AccreditationTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;

    public const TEST_URL = '/api/v1/accreditations';

    private $user = null;
    private $registry = null;
    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserSeeder::class,
        ]);

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer '.$this->getAuthToken(),
        ];

        $this->user = User::where('id', 1)->first();
        $this->registry = Registry::where('id', $this->user->registry_id)->first();

    }

    public function test_the_application_can_list_accreditations_by_registry_id(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL . '/' . $this->registry->id,
            $this->headers
        );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertArrayHaskey('data', $response);
        $this->assertTrue(count($content['data']) === 1);
        $this->assertEquals($content['data'][0]['title'], 'Safe Researcher Training');
    }

    public function test_the_application_can_create_accreditations_by_registry_id(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL . '/' . $this->registry->id,
            $this->prepareAccreditationPayload(),
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertEquals($response->decodeResponseJson()['message'], 'success');
    }

    public function test_the_application_can_edit_accreditations_by_registry_id(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL . '/' . $this->registry->id,
            $this->prepareAccreditationPayload(),
            $this->headers
        );

        $content = $response->decodeResponseJson();

        $response->assertStatus(201);
        $this->assertEquals($content['message'], 'success');

        $response = $this->json(
            'PATCH',
            self::TEST_URL . '/' . $content['data'] . '/' . $this->registry->id,
            $this->prepareEditedAccreditationPayload(),
            $this->headers
        );

        $content = $response->decodeResponseJson();

        $response->assertStatus(200);

        $this->assertEquals($content['data']['title'], 'Safe Researcher Training - The Sequel!');
        $this->assertEquals($content['data']['awarded_locale'], 'GB');
    }

    public function test_the_application_can_update_accreditations_by_registry_id(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL . '/' . $this->registry->id,
            $this->prepareAccreditationPayload(),
            $this->headers
        );

        $content = $response->decodeResponseJson();

        $response->assertStatus(201);
        $this->assertEquals($content['message'], 'success');

        $response = $this->json(
            'PUT',
            self::TEST_URL . '/' . $content['data'] . '/' . $this->registry->id,
            $this->prepareUpdatedAccreditationPayload(),
            $this->headers
        );

        $content = $response->decodeResponseJson();

        $response->assertStatus(200);

        $this->assertEquals($content['data']['title'], 'Safe Researcher Training. The Sequel!!');
        $this->assertEquals($content['data']['awarded_locale'], 'UK');
    }

    public function test_the_application_can_delete_accreditations_by_registry_id(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL . '/' . $this->registry->id,
            $this->prepareAccreditationPayload(),
            $this->headers
        );

        $content = $response->decodeResponseJson();

        $response->assertStatus(201);
        $this->assertEquals($content['message'], 'success');

        $response = $this->json(
            'DELETE',
            self::TEST_URL . '/' . $content['data'] . '/' . $this->registry->id,
            $this->headers
        );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();
        $this->assertEquals($content['message'], 'success');
    }

    private function prepareAccreditationPayload(): array
    {
        $awardedDate = Carbon::parse(fake()->date());

        return [
            'awarded_at' => $awardedDate->toDateString(),
            'awarding_body_name' => fake()->company(),
            'awarding_body_ror' => fake()->url(),
            'title' => 'Safe Researcher Training',
            'expires_at' => $awardedDate->addYear(2)->toDateString(),
            'awarded_locale' => 'GB',
        ];
    }

    private function prepareUpdatedAccreditationPayload(): array
    {
        $awardedDate = Carbon::parse(fake()->date());

        return [
            'awarded_at' => $awardedDate->toDateString(),
            'awarding_body_name' => fake()->company(),
            'awarding_body_ror' => fake()->url(),
            'title' => 'Safe Researcher Training. The Sequel!!',
            'expires_at' => $awardedDate->addYear(4)->toDateString(),
            'awarded_locale' => 'UK',
        ];
    }

    private function prepareEditedAccreditationPayload(): array
    {
        return [
            'title' => 'Safe Researcher Training - The Sequel!',
        ];
    }
}
