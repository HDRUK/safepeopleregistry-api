<?php

namespace Tests\Feature;

use App\Models\ProjectHasUser;
use App\Models\ValidationCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationCheckTest extends TestCase
{
    use RefreshDatabase;

    public const TEST_URL = '/api/v1/validation_checks';

    protected function setUp(): void
    {
        parent::setUp();
        ValidationCheck::truncate();
    }

    public function test_it_can_list_all_validation_checks()
    {
        $checks = ValidationCheck::factory()->count(3)->create();

        $response = $this->getJson(self::TEST_URL);

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_it_can_show_a_single_validation_check()
    {
        $check = ValidationCheck::factory()->create();

        $response = $this->getJson(self::TEST_URL . "/{$check->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $check->id,
                    'name' => $check->name,
                    'description' => $check->description,
                ]
            ]);
    }

    public function test_it_returns_404_when_showing_missing_validation_check()
    {
        $response = $this->getJson(self::TEST_URL . "/999");

        $response->assertNotFound()
            ->assertJson([
                'message' => 'not found',
            ]);
    }

    public function test_it_can_create_a_validation_check()
    {
        $payload = [
            'name' => 'test_check',
            'description' => 'Test check description',
            'applies_to' => ProjectHasUser::class
        ];

        $response = $this->postJson(self::TEST_URL, $payload);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'name' => $payload['name'],
                    'description' => $payload['description'],
                ]
            ]);

        $this->assertDatabaseHas('validation_checks', $payload);
    }

    public function test_it_can_update_a_validation_check()
    {
        $check = ValidationCheck::factory()->create([
            'name' => 'original_name',
            'description' => 'original_description',
            'applies_to' => ProjectHasUser::class
        ]);

        $payload = [
            'name' => 'updated_name',
            'description' => 'updated_description',
        ];

        $response = $this->putJson(self::TEST_URL . "/{$check->id}", $payload);

        $response->assertOk()
            ->assertJson([
                'data' => $payload
            ]);

        $this->assertDatabaseHas('validation_checks', $payload);
    }

    public function test_it_returns_404_when_updating_missing_validation_check()
    {
        $payload = [
            'name' => 'irrelevant',
            'description' => 'irrelevant',
        ];

        $response = $this->putJson(self::TEST_URL . "/999", $payload);

        $response->assertNotFound()
            ->assertJson([
                'message' => 'not found',
            ]);
    }

    public function test_it_can_delete_a_validation_check()
    {
        $check = ValidationCheck::factory()->create();

        $response = $this->deleteJson(self::TEST_URL . "/{$check->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('validation_checks', ['id' => $check->id]);
    }

    public function test_it_returns_404_when_deleting_missing_validation_check()
    {
        $response = $this->deleteJson(self::TEST_URL . "/999");

        $response->assertNotFound()
            ->assertJson([
                'message' => 'not found',
            ]);
    }
}
