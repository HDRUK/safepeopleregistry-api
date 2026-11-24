<?php

namespace Tests\Feature;

use App\Http\Traits\HmacSigning;
use App\Models\Custodian;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class VerifySignedCustodianRequestTest extends TestCase
{
    use HmacSigning;

    private const TEST_PATH = '/test/custodian-signed-request';

    private Custodian $custodian;

    public function setUp(): void
    {
        parent::setUp();
        $this->withMiddleware();
        Carbon::setTestNow(Carbon::create(2026, 11, 23, 12));

        Route::middleware(['verify.custodian.signed.request', 'check.custodian.access'])
            ->get(self::TEST_PATH, fn (Request $request): JsonResponse => response()->json([
                'custodian_id' => $request->attributes->get('custodian')->id,
            ]));

        // The factory randomises `enabled`
        $this->custodian = Custodian::factory()->create(['enabled' => true]);
    }

    public function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_a_request_without_a_timestamp_is_rejected(): void
    {
        $headers = $this->signedHeaders(['page' => '1']);
        unset($headers['x-timestamp']);

        $this->send(['page' => '1'], $headers)->assertStatus(401);
    }

    public function test_a_request_without_a_signature_is_rejected(): void
    {
        $headers = $this->signedHeaders(['page' => '1']);
        unset($headers['x-signature']);

        $this->send(['page' => '1'], $headers)->assertStatus(401);
    }

    public function test_a_non_numeric_timestamp_is_rejected(): void
    {
        $headers = $this->signedHeaders(['page' => '1'], timestamp: '2026-11-23T12:00:00Z');

        $this->send(['page' => '1'], $headers)->assertStatus(401);
    }

    public function test_a_replayed_request_older_than_the_tolerance_is_rejected(): void
    {
        $headers = $this->signedHeaders(['page' => '1'], timestamp: (string) Carbon::now()->subSeconds(301)->timestamp);

        $this->send(['page' => '1'], $headers)->assertStatus(401);
    }

    public function test_a_timestamp_too_far_in_the_future_is_rejected(): void
    {
        $headers = $this->signedHeaders(['page' => '1'], timestamp: (string) Carbon::now()->addSeconds(301)->timestamp);

        $this->send(['page' => '1'], $headers)->assertStatus(401);
    }

    public function test_a_signature_made_with_the_wrong_secret_is_rejected(): void
    {
        $headers = $this->signedHeaders(['page' => '1'], secret: 'not-the-custodians-secret');

        $this->send(['page' => '1'], $headers)->assertStatus(401);
    }

    public function test_a_signature_cannot_be_reused_for_a_different_query(): void
    {
        $headers = $this->signedHeaders(['page' => '2']);

        $this->send(['page' => '3'], $headers)->assertStatus(401);
    }

    public function test_a_signature_cannot_be_reused_for_a_different_path(): void
    {
        $headers = $this->signedHeaders(['page' => '1'], path: '/test/some-other-route');

        $this->send(['page' => '1'], $headers)->assertStatus(401);
    }

    public function test_a_disabled_custodian_is_rejected_even_with_a_valid_signature(): void
    {
        $this->custodian->update(['enabled' => false]);

        $this->send(['page' => '1'], $this->signedHeaders(['page' => '1']))->assertStatus(401);
    }

    public function test_a_validly_signed_request_is_bound_to_its_custodian(): void
    {
        $this->send(['page' => '1'], $this->signedHeaders(['page' => '1']))
            ->assertStatus(200)
            ->assertJsonPath('custodian_id', $this->custodian->id);
    }

    public function test_the_order_of_query_parameters_does_not_matter(): void
    {
        $headers = $this->signedHeaders(['per_page' => '50', 'page' => '2']);

        $this->send(['page' => '2', 'per_page' => '50'], $headers)->assertStatus(200);
    }

    public function test_a_timestamp_at_the_edge_of_the_tolerance_is_accepted(): void
    {
        $headers = $this->signedHeaders(['page' => '1'], timestamp: (string) Carbon::now()->subSeconds(300)->timestamp);

        $this->send(['page' => '1'], $headers)->assertStatus(200);
    }

    public function test_a_signature_built_from_the_documented_canonical_string_is_accepted(): void
    {
        // Built by hand, not with the shared helper, so this pins the spec custodians implement
        $timestamp = (string) Carbon::now()->timestamp;
        $canonical = $timestamp . "\nGET\n" . self::TEST_PATH . "\npage=2&per_page=50";
        $signature = base64_encode(hash_hmac('sha256', $canonical, $this->custodian->unique_identifier, true));

        $this->send(['per_page' => '50', 'page' => '2'], [
            'x-client-id' => $this->custodian->client_id,
            'x-timestamp' => $timestamp,
            'x-signature' => $signature,
        ])->assertStatus(200);
    }

    /**
     * @param array<string, string> $query
     * @return array<string, string>
     */
    private function signedHeaders(
        array $query,
        ?string $timestamp = null,
        ?string $secret = null,
        string $path = self::TEST_PATH,
    ): array {
        $timestamp ??= (string) Carbon::now()->timestamp;

        return [
            'x-client-id' => $this->custodian->client_id,
            'x-timestamp' => $timestamp,
            'x-signature' => $this->generateCanonicalRequestSignature(
                $timestamp,
                'GET',
                $path,
                $query,
                $secret ?? $this->custodian->unique_identifier,
            ),
        ];
    }

    /**
     * @param array<string, string> $query
     * @param array<string, string> $headers
     */
    private function send(array $query, array $headers): TestResponse
    {
        return $this->getJson(self::TEST_PATH . '?' . http_build_query($query), $headers);
    }
}
