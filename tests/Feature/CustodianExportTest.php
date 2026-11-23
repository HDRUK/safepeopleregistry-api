<?php

namespace Tests\Feature;

use App\Http\Traits\HmacSigning;
use App\Models\Accreditation;
use App\Models\Affiliation;
use App\Models\Custodian;
use App\Models\CustodianHasProjectUser;
use App\Models\Identity;
use App\Models\Project;
use App\Models\ProjectHasCustodian;
use App\Models\ProjectHasUser;
use App\Models\Registry;
use App\Models\State;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CustodianExportTest extends TestCase
{
    use HmacSigning;

    private const PROJECTS_PATH = '/api/v1/custodian/export/projects';
    private const USERS_PATH = '/api/v1/custodian/export/users';

    private Custodian $custodianA;
    private Custodian $custodianB;
    private Project $projectA;
    private Project $projectB;
    private Registry $onlyA;
    private Registry $onlyB;
    private Registry $both;

    public function setUp(): void
    {
        parent::setUp();
        $this->withMiddleware();

        $this->custodianA = Custodian::factory()->create(['enabled' => true]);
        $this->custodianB = Custodian::factory()->create(['enabled' => true]);
        $this->projectA = $this->projectFor($this->custodianA);
        $this->projectB = $this->projectFor($this->custodianB);

        $this->onlyA = $this->makeResearcher('only-a');
        $this->onlyB = $this->makeResearcher('only-b');
        $this->both = $this->makeResearcher('both');

        $this->addToProject($this->onlyA, $this->projectA, $this->custodianA, State::STATE_VALIDATION_COMPLETE);
        $this->addToProject($this->onlyB, $this->projectB, $this->custodianB, State::STATE_PENDING);
        $this->addToProject($this->both, $this->projectA, $this->custodianA, State::STATE_VALIDATION_IN_PROGRESS);
        $this->addToProject($this->both, $this->projectB, $this->custodianB, State::STATE_PENDING);
    }

    public static function exportPaths(): array
    {
        return [
            'projects' => [self::PROJECTS_PATH],
            'users' => [self::USERS_PATH],
        ];
    }

    #[DataProvider('exportPaths')]
    public function test_an_unsigned_request_is_rejected(string $path): void
    {
        $this->getJson($path, ['x-client-id' => $this->custodianA->client_id])->assertStatus(401);
    }

    public function test_a_custodian_only_sees_its_own_projects(): void
    {
        $ids = collect($this->export($this->custodianA, self::PROJECTS_PATH)->assertStatus(200)->json('data.data'))
            ->pluck('id');

        $this->assertTrue($ids->contains($this->projectA->id));
        $this->assertFalse($ids->contains($this->projectB->id));
    }

    public function test_a_custodian_only_sees_users_on_its_own_projects(): void
    {
        $idents = collect($this->export($this->custodianA, self::USERS_PATH)->assertStatus(200)->json('data.data'))
            ->pluck('digi_ident');

        $this->assertEqualsCanonicalizing([$this->onlyA->digi_ident, $this->both->digi_ident], $idents->all());
    }

    public function test_a_user_on_both_custodians_projects_only_shows_this_custodians_projects(): void
    {
        $both = $this->exportedUser($this->custodianA, $this->both);

        $this->assertSame([$this->projectA->id], array_column($both['projects'], 'project_id'));
    }

    public function test_the_export_contains_no_contact_or_identity_document_details(): void
    {
        $content = $this->export($this->custodianA, self::USERS_PATH)->assertStatus(200)->getContent();

        foreach ([$this->onlyA, $this->both] as $registry) {
            $this->assertStringNotContainsString($registry->user->email, $content);
            $this->assertStringNotContainsString($registry->affiliations->first()->email, $content);
            $this->assertStringNotContainsString($registry->affiliations->first()->member_id, $content);
        }

        foreach (['ABCDE123456A99AA 12', '"123456789"', '123 Road', 'AB12 3CD', '1977-07-25', 'PASSPORT-NOT-EXPORTED', 'CERT-NOT-EXPORTED', 'ACCREDITATION-ID-NOT-EXPORTED'] as $value) {
            $this->assertStringNotContainsString($value, $content);
        }
    }

    public function test_a_page_size_over_the_limit_is_rejected(): void
    {
        $this->export($this->custodianA, self::PROJECTS_PATH, ['per_page' => '101'])->assertStatus(400);
    }

    public function test_projects_include_their_status_and_those_without_users(): void
    {
        $empty = $this->projectFor($this->custodianA);
        $empty->setState(State::STATE_PROJECT_PENDING);

        $projects = collect($this->export($this->custodianA, self::PROJECTS_PATH)->assertStatus(200)->json('data.data'))
            ->keyBy('id');

        $this->assertSame($empty->title, $projects[$empty->id]['title']);
        $this->assertSame(State::STATE_PROJECT_PENDING, $projects[$empty->id]['status']['slug']);
    }

    public function test_users_include_their_profile_and_validation_status_per_project(): void
    {
        $user = $this->exportedUser($this->custodianA, $this->onlyA);

        $this->assertSame($this->onlyA->user->first_name, $user['first_name']);
        $this->assertSame($this->onlyA->user->last_name, $user['last_name']);
        $this->assertSame('0000-0002-1825-0097', $user['orcid']);
        $this->assertTrue($user['identity_verified']);
        $this->assertSame('employment', $user['affiliations'][0]['relationship']);
        $this->assertSame('Exported training', $user['trainings'][0]['training_name']);
        $this->assertSame('Exported accreditor', $user['accreditations'][0]['associated_organisation_name']);
        $this->assertSame($this->projectA->title, $user['projects'][0]['project_title']);
        $this->assertSame(State::STATE_VALIDATION_COMPLETE, $user['projects'][0]['validation_status']['slug']);
    }

    public function test_each_project_shows_which_affiliation_links_the_user_to_it(): void
    {
        $linkingAffiliation = Affiliation::factory()->create([
            'registry_id' => $this->onlyA->id,
            'relationship' => 'honorary_contract',
            'current_employer' => true,
        ]);
        $otherProject = $this->projectFor($this->custodianA);
        $this->addToProject($this->onlyA, $otherProject, $this->custodianA, State::STATE_PENDING, $linkingAffiliation);

        $user = $this->exportedUser($this->custodianA, $this->onlyA);
        $projects = collect($user['projects'])->keyBy('project_id');
        $linked = $projects[$otherProject->id]['affiliation'];

        $this->assertSame($linkingAffiliation->id, $linked['id']);
        $this->assertSame($linkingAffiliation->organisation->organisation_name, $linked['organisation_name']);
        $this->assertSame('honorary_contract', $linked['relationship']);
        $this->assertTrue($linked['current_employer']);
        $this->assertNull($projects[$this->projectA->id]['affiliation']);
        $this->assertCount(2, $user['affiliations']);
    }

    public function test_users_are_paginated(): void
    {
        $this->export($this->custodianA, self::USERS_PATH, ['per_page' => '1'])
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.total', 2);
    }

    public function test_each_projects_export_request_is_audited(): void
    {
        Log::spy();

        $this->export($this->custodianA, self::PROJECTS_PATH)->assertStatus(200);

        Log::shouldHaveReceived('info')->withArgs(fn ($message, $context) => $message === 'CustodianExport'
            && $context['custodian_id'] === $this->custodianA->id
            && $context['endpoint'] === 'projects'
            && $context['count'] === 1);
    }

    public function test_each_users_export_request_is_audited(): void
    {
        Log::spy();

        $this->export($this->custodianA, self::USERS_PATH)->assertStatus(200);

        Log::shouldHaveReceived('info')->withArgs(fn ($message, $context) => $message === 'CustodianExport'
            && $context['custodian_id'] === $this->custodianA->id
            && $context['endpoint'] === 'users'
            && $context['count'] === 2);
    }

    /**
     * @param array<string, string> $query
     */
    private function export(Custodian $custodian, string $path, array $query = []): TestResponse
    {
        $timestamp = (string) now()->timestamp;

        return $this->getJson($path . ($query ? '?' . http_build_query($query) : ''), [
            'x-client-id' => $custodian->client_id,
            'x-timestamp' => $timestamp,
            'x-signature' => $this->generateCanonicalRequestSignature(
                $timestamp,
                'GET',
                $path,
                $query,
                $custodian->unique_identifier
            ),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function exportedUser(Custodian $custodian, Registry $registry): array
    {
        return collect($this->export($custodian, self::USERS_PATH)->assertStatus(200)->json('data.data'))
            ->firstWhere('digi_ident', $registry->digi_ident);
    }

    private function projectFor(Custodian $custodian): Project
    {
        $project = Project::factory()->create();
        ProjectHasCustodian::create(['project_id' => $project->id, 'custodian_id' => $custodian->id]);

        return $project;
    }

    private function makeResearcher(string $name): Registry
    {
        $registry = Registry::factory()->create();
        User::factory()->create([
            'registry_id' => $registry->id,
            'user_group' => User::GROUP_USERS,
            'first_name' => $name,
            'email' => "{$name}@example.com",
            'orc_id' => '0000-0002-1825-0097',
        ]);
        // IdentityFactory sets a column (idvt_result) that no longer exists
        Identity::create([
            'registry_id' => $registry->id,
            'address_1' => '123 Road',
            'postcode' => 'AB12 3CD',
            'dob' => '1977-07-25',
            'idvt_success' => 1,
            'idvt_document_number' => 'PASSPORT-NOT-EXPORTED',
        ]);
        Affiliation::factory()->create([
            'registry_id' => $registry->id,
            'relationship' => 'employment',
            'email' => "{$name}.work@example.com",
        ]);
        $registry->trainings()->attach(Training::factory()->create([
            'training_name' => 'Exported training',
            'certification_id' => 'CERT-NOT-EXPORTED',
        ])->id);
        $registry->accreditations()->attach(Accreditation::factory()->create([
            'associated_organisation_name' => 'Exported accreditor',
            'id_string' => 'ACCREDITATION-ID-NOT-EXPORTED',
        ])->id);

        return $registry->fresh();
    }

    private function addToProject(
        Registry $registry,
        Project $project,
        Custodian $custodian,
        string $state,
        ?Affiliation $affiliation = null,
    ): void {
        $projectUser = ProjectHasUser::create([
            'project_id' => $project->id,
            'user_digital_ident' => $registry->digi_ident,
            'project_role_id' => 1,
            'affiliation_id' => $affiliation?->id,
        ]);

        CustodianHasProjectUser::create([
            'custodian_id' => $custodian->id,
            'project_has_user_id' => $projectUser->id,
        ])->setState($state);
    }
}
