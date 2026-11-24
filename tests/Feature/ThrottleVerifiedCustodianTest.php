<?php

namespace Tests\Feature;

use App\Http\Traits\HmacSigning;
use App\Models\Custodian;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ThrottleVerifiedCustodianTest extends TestCase
{
    use HmacSigning;

    private const TEST_PATH = '/test/throttle-verified-custodian';
    private const UNVERIFIED_PATH = '/test/throttle-unverified-custodian';

    private Custodian $custodianA;
    private Custodian $custodianB;

    public function setUp(): void
    {
        parent::setUp();
        $this->withMiddleware();
        config(['speedi.system.custodian_export_rate_limit' => 2]);

        // The api group is what makes Laravel re-sort ThrottleRequests ahead of route middleware
        Route::middleware(['api', 'verify.custodian.signed.request', 'check.custodian.access', 'throttle.verified.custodian'])
            ->get(self::TEST_PATH, fn (): JsonResponse => response()->json(['ok' => true]));
        Route::middleware(['api', 'throttle.verified.custodian'])
            ->get(self::UNVERIFIED_PATH, fn (): JsonResponse => response()->json(['ok' => true]));

        $this->custodianA = Custodian::factory()->create(['enabled' => true]);
        $this->custodianB = Custodian::factory()->create(['enabled' => true]);
    }

    public function test_requests_with_a_bad_signature_do_not_use_up_the_custodians_quota(): void
    {
        foreach (range(1, 3) as $attempt) {
            $this->getJson(self::TEST_PATH, [
                'x-client-id' => $this->custodianA->client_id,
                'x-timestamp' => (string) Carbon::now()->timestamp,
                'x-signature' => 'not-a-valid-signature',
            ])->assertStatus(401);
        }

        $this->signedGet($this->custodianA)->assertStatus(200);
    }

    public function test_requests_are_refused_when_no_custodian_has_been_verified(): void
    {
        $this->getJson(self::UNVERIFIED_PATH)->assertStatus(401);
    }

    public function test_the_limit_applies_per_custodian_not_per_ip(): void
    {
        $this->signedGet($this->custodianA)->assertStatus(200);
        $this->signedGet($this->custodianA)->assertStatus(200);
        $this->signedGet($this->custodianA)->assertStatus(429)->assertHeader('Retry-After');

        $this->signedGet($this->custodianB)->assertStatus(200);
    }

    private function signedGet(Custodian $custodian): TestResponse
    {
        $timestamp = (string) Carbon::now()->timestamp;

        return $this->getJson(self::TEST_PATH, [
            'x-client-id' => $custodian->client_id,
            'x-timestamp' => $timestamp,
            'x-signature' => $this->generateCanonicalRequestSignature(
                $timestamp,
                'GET',
                self::TEST_PATH,
                [],
                $custodian->unique_identifier
            ),
        ]);
    }
}
