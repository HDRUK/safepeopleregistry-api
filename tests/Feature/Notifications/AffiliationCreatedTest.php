<?php

namespace Tests\Feature\Notifications;

use Tests\TestCase;
use App\Models\User;
use App\Models\Registry;
use App\Models\Affiliation;
use App\Models\Organisation;
use App\Notifications\Affiliations\AffiliationCreated;

class AffiliationCreatedTest extends TestCase
{
    private User $researcher;
    private Affiliation $affiliation;

    protected function setUp(): void
    {
        parent::setUp();

        $registry = Registry::factory()->create();
        $this->researcher = User::factory()->create([
            'user_group' => User::GROUP_USERS,
            'registry_id' => $registry->id,
        ]);
        $this->affiliation = Affiliation::factory()->create([
            'organisation_id' => Organisation::factory()->create()->id,
            'registry_id' => $registry->id,
        ]);
    }

    public function test_an_affiliation_request_links_the_organisation_to_the_users_profile(): void
    {
        $data = (new AffiliationCreated($this->researcher, $this->affiliation, 'organisation', true))
            ->toDatabase($this->researcher);

        $this->assertEquals(['type' => 'organisation_user', 'id' => $this->researcher->id], $data['link']);
        $this->assertStringNotContainsString('[Button', $data['message']);
    }

    public function test_other_affiliation_notifications_have_no_link(): void
    {
        foreach ([['user', true], ['custodian', true], ['organisation', false]] as [$for, $isRequest]) {
            $data = (new AffiliationCreated($this->researcher, $this->affiliation, $for, $isRequest))
                ->toDatabase($this->researcher);

            $this->assertArrayNotHasKey('link', $data, "{$for} should have no link");
        }
    }
}
