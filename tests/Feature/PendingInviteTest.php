<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Affiliation;
use App\Models\PendingInvite;
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

        $currDate = Carbon::today()->format('Y-m-d');

        PendingInvite::where('id', 1)->update([
            'invite_sent_at' => Carbon::now()->subDay(),
        ]);
        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . '?invite_sent_at=' . $currDate
            );

        $response->assertStatus(200);
        $data = $response->decodeResponseJson()['data']['data'];
        $this->assertEquals(2, count($data));
    }

    public function test_the_application_can_sort_pending_invites_by_name_desc_irrespective_of_invite_date(): void
    {
        $invites = $this->createNamedInvites();

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . '?sort=users.name:desc'
            );

        $response->assertStatus(200);
        $data = $response->decodeResponseJson()['data']['data'];

        $this->assertEquals(
            ['Zed older', 'Zed newer', 'Alex'],
            $this->inviteLabels($invites, $data)
        );
    }

    public function test_the_application_can_sort_pending_invites_by_name_asc_irrespective_of_invite_date(): void
    {
        $invites = $this->createNamedInvites();

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . '?sort=users.name:asc'
            );

        $response->assertStatus(200);
        $data = $response->decodeResponseJson()['data']['data'];

        $this->assertEquals(
            ['Alex', 'Zed older', 'Zed newer'],
            $this->inviteLabels($invites, $data)
        );
    }

    public function test_the_application_can_filter_pending_invites_when_sorted_by_name(): void
    {
        $invites = $this->createNamedInvites();
        $currDate = Carbon::today()->format('Y-m-d');

        $filters = [
            'user_group=USERS' => 3,
            'email=' . $invites['Alex']->user->email => 1,
            'status[]__and=' . PendingInvite::STATE_PENDING => 3,
            'invite_sent_at[]__and=' . $currDate => 1,
            'updated_at=' . $currDate => 3,
        ];

        foreach ($filters as $filter => $expectedCount) {
            $response = $this->actingAs($this->admin)
                ->json(
                    'GET',
                    self::TEST_URL . '?sort=users.name:desc&' . $filter
                );

            $response->assertStatus(200);
            $data = $response->decodeResponseJson()['data']['data'];
            $this->assertEquals($expectedCount, count($data), $filter);
        }
    }

    /**
     * Creates three invites: "Alex" sent today, and two invites for users
     * both named "Zed", one sent yesterday ("Zed newer") and one sent two
     * days ago ("Zed older").
     *
     * @return array<string, PendingInvite>
     */
    private function createNamedInvites(): array
    {
        foreach ($this->generateListInvites() as $invite) {
            $this->inviteUser($invite)->assertStatus(201);
        }

        $pendingInvites = PendingInvite::with('user')->orderBy('id')->get();
        $this->assertCount(3, $pendingInvites);

        $invites = [
            'Alex' => [$pendingInvites[0], 'Alex', Carbon::now()],
            // The newer invite has the lower id, so a name sort that ignores
            // invite_sent_at (ties broken by id desc) puts it last.
            'Zed newer' => [$pendingInvites[1], 'Zed', Carbon::now()->subDay()],
            'Zed older' => [$pendingInvites[2], 'Zed', Carbon::now()->subDays(2)],
        ];

        foreach ($invites as [$pendingInvite, $name, $inviteSentAt]) {
            // name is generated from first_name and last_name
            $pendingInvite->user->update(['first_name' => $name, 'last_name' => 'Smith']);
            $pendingInvite->update(['invite_sent_at' => $inviteSentAt]);
        }

        return array_map(fn ($invite) => $invite[0]->fresh('user'), $invites);
    }

    private function inviteLabels(array $invites, array $data): array
    {
        $labels = array_flip(array_map(fn ($invite) => $invite->id, $invites));

        return array_map(fn ($invite) => $labels[$invite['id']], $data);
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
