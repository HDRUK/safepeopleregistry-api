<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\Models\Rule;
use App\Models\RuleHasCondition;

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Traits\Authroisation;

class RuleTest extends TestCase
{
    use RefreshDatabase, Authorisation;

    const TEST_URL = '/api/v1/rules';

    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer ' . $this->getAuthToken(),
        ];
    }

    public function test_the_application_can_list_rules(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL,
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }
}