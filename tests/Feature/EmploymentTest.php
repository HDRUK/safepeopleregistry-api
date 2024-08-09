<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Registry;

use Database\Seeders\UserSeeder;
use Database\Seeders\EmploymentSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

use Tests\Traits\Authorisation;

class EmploymentTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;

    public const TEST_URL = '/api/v1/employments';

    private $user = null;
    private $registry = null;
    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserSeeder::class,
            EmploymentSeeder::class,
        ]);

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer '.$this->getAuthToken(),
        ];

        $this->user = User::where('id', 1)->first();
        $this->registry = Registry::where('id', $this->user->registry_id)->first();

    }

    public function test_the_application_can_list_employments_by_registry_id(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL . '/' . $this->registry->id,
            $this->headers
        );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertArrayHaskey('data', $response);
    }

    public function test_the_application_can_create_employments_by_registry_id(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL . '/' . $this->registry->id,
            $this->prepareEmploymentPayload(),
            $this->headers
        );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson();

        $this->assertEquals($content['message'], 'success');
        $this->assertNotNull($content['data']);
    }

    public function test_the_application_can_edit_employments_by_registry_id(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL . '/' . $this->registry->id,
            $this->prepareEmploymentPayload(),
            $this->headers
        );

        $content = $response->decodeResponseJson();

        $response->assertStatus(201);
        $this->assertEquals($content['message'], 'success');

        $response = $this->json(
            'PATCH',
            self::TEST_URL . '/' . $content['data'] . '/' . $this->registry->id,
            $this->prepareEditedEmploymentPayload(),
            $this->headers
        );

        $content = $response->decodeResponseJson();

        $response->assertStatus(200);

        $this->assertEquals(
            $content['data']['employer_address'],
            '123 Madeup Street, Someplace, Somewhere, T35T 1NG'
        );
    }

    public function test_the_application_can_update_employments_by_registry_id(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL . '/' . $this->registry->id,
            $this->prepareEmploymentPayload(),
            $this->headers
        );

        $content = $response->decodeResponseJson();

        $response->assertStatus(201);
        $this->assertEquals($content['message'], 'success');

        $response = $this->json(
            'PUT',
            self::TEST_URL . '/' . $content['data'] . '/' . $this->registry->id,
            $this->prepareUpdatedEmploymentPayload(),
            $this->headers
        );

        $content = $response->decodeResponseJson();

        $response->assertStatus(200);

        $this->assertEquals($content['data']['employer_name'], 'Demo Employer Name 2');
    }

    public function test_the_application_can_delete_employments_by_registry_id(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL . '/' . $this->registry->id,
            $this->prepareEmploymentPayload(),
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

    private function prepareEmploymentPayload(): array
    {
        return [
            'employer_name' => 'Demo Employer Name',
            'from' => fake()->date(),
            'to' => fake()->date(),
            'is_current' => fake()->randomElement([0, 1]),
            'department' => fake()->sentence(2),
            'role' => fake()->sentence(3),
            'employer_address' => fake()->address(),
            'ror' => fake()->url(),
            'registry_id' => 1,
        ];
    }

    private function prepareUpdatedEmploymentPayload(): array
    {
        return [
            'employer_name' => 'Demo Employer Name 2',
            'from' => fake()->date(),
            'to' => fake()->date(),
            'is_current' => fake()->randomElement([0, 1]),
            'department' => fake()->sentence(2),
            'role' => fake()->sentence(3),
            'employer_address' => fake()->address(),
            'ror' => fake()->url(),
            'registry_id' => 1,
        ];
    }

    private function prepareEditedEmploymentPayload(): array
    {
        return [
            'employer_address' => '123 Madeup Street, Someplace, Somewhere, T35T 1NG',
        ];
    }
}
