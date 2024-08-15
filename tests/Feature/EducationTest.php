<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;
use Tests\Traits\Authorisation;

class EducationTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;

    public const TEST_URL = '/api/v1/educations';

    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserSeeder::class,
        ]);

        
    }
}