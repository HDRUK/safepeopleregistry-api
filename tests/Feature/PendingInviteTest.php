<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Affiliation;
use Tests\Traits\Authorisation;

class PendingInviteTest extends TestCase
{
    use Authorisation;

    public const TEST_URL = '/api/v1/pending_invites';

    public function setUp(): void
    {
        parent::setUp();
        $this->withMiddleware();
        $this->withUsers();
    }

    private function inviteUser(array $user): mixed
    {
        return $this->actingAs($this->admin)
            ->json(
                'POST',
                '/api/v1/organisations/1/invite_user',
                $user
            );
    }

    public function test_the_application_can_list_pending_invites(): void
    {
        $response = $this->inviteUser([
            'first_name' => "Alice",
            'last_name' => "Johnson",
            'email' => "alice.johnson@hdruk.ac.uk",
        ]);

        $response->assertStatus(201);

        $response = $this->inviteUser([
            'first_name' => "John",
            'last_name' => "Smith",
            'email' => "john.smith@hdruk.ac.uk",
        ]);

        $response->assertStatus(201);

        Affiliation::latest()->take(1)->delete();

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL
            );

        $response->assertStatus(200);

        $data = $response->decodeResponseJson()['data']['data'];

        $this->assertEquals(2, count($data));
    }

    public function test_the_application_can_search_pending_invites(): void
    {
        $inviteList = $this->generateListInvites();

        foreach ($inviteList as $invite) {
            $response = $this->inviteUser($invite);
            $response->assertStatus(201);
        }

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . '?user_group=USERS'
            );

        $response->assertStatus(200);
        $data = $response->decodeResponseJson()['data']['data'];
        $this->assertEquals(3, count($data));

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . '?email=' . $inviteList[0]['email']
            );

        $response->assertStatus(200);
        $data = $response->decodeResponseJson()['data']['data'];
        $this->assertEquals(1, count($data));

        var_dump($this->generateListInvites());
    }

    private function generateListInvites()
    {
        $response = [];

        for ($x = 0; $x <= 2; $x++) {
            $response[] = [
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => fake()->email(),
            ];
        }

        return $response;

    }
}
