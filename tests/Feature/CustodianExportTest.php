<?php

namespace Tests\Feature;

use App\Http\Traits\HmacSigning;
use App\Models\Custodian;
use App\Models\Project;
use App\Models\ProjectHasCustodian;
use App\Models\State;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CustodianExportTest extends TestCase
{
    use HmacSigning;

    private const PROJECTS_PATH = '/api/v1/custodian/export/projects';

    private Custodian $custodianA;
    private Custodian $custodianB;
    private Project $projectA;
    private Project $projectB;

    public function setUp(): void
    {
        parent::setUp();
        $this->withMiddleware();

        $this->custodianA = Custodian::factory()->create(['enabled' => true]);
        $this->custodianB = Custodian::factory()->create(['enabled' => true]);
        $this->projectA = $this->projectFor($this->custodianA);
        $this->projectB = $this->projectFor($this->custodianB);
    }

    public static function exportPaths(): array
    {
        return [
            'projects' => [self::PROJECTS_PATH],
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

    public function test_each_projects_export_request_is_audited(): void
    {
        Log::spy();

        $this->export($this->custodianA, self::PROJECTS_PATH)->assertStatus(200);

        Log::shouldHaveReceived('info')->withArgs(fn ($message, $context) => $message === 'CustodianExport'
            && $context['custodian_id'] === $this->custodianA->id
            && $context['endpoint'] === 'projects'
            && $context['count'] === 1);
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

    private function projectFor(Custodian $custodian): Project
    {
        $project = Project::factory()->create();
        ProjectHasCustodian::create(['project_id' => $project->id, 'custodian_id' => $custodian->id]);

        return $project;
    }
}
