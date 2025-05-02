<?php

namespace App\Services\WebhookTranslators;

use Carbon\Carbon;
use App\Models\DebugLog;
use App\Models\Identity;
use App\Models\Registry;
use Illuminate\Http\Request;
use App\Http\Traits\HmacSigning;

class VeriffTranslator implements WebhookTranslationInterface
{
    use HmacSigning;

    public function validateSignature(Request $request): bool
    {
        DebugLog::create([
            'class' => VeriffTranslator::class,
            'log' => 'Veriff signature validation - ' . json_encode($request->json()->all()) . ' - ' . $request->header('x-hmac-signature') . ' - ' . $this->generateSignature(
                $request->json()->all(),
                env('IDVT_SUPPLIER_SECRET_KEY')
            ),
        ]);

        $verdict = $this->verifySignature(
            $request->json()->all(),
            env('IDVT_SUPPLIER_SECRET_KEY'),
            $request->header('x-hmac-signature')
        );

        return $verdict;
    }

    public function translate(array $data): array
    {
        return [
            'status' => $data['status'] ?? null,
            'verification' => $data['verification'] ?? null,
            'verified' => $data['verification']['status'] ?? null,
            'decision_time' => $data['verification']['decision_time'] ?? null,
            'registry_id' => $data['verification']['vendorData'] ?? null,
        ];
    }

    public function saveContext(array $data): void
    {
        $registry = Registry::where('digi_ident', $data['registry_id'])->first();
        if (!$registry) {
            DebugLog::create([
                'class' => VeriffTranslator::class,
                'log' => 'Attempt to save veriff context with unknown registry_id - ' . json_encode($data),
            ]);

            return;
        }

        $identity = Identity::where('registry_id', $registry->id)->first();
        if (!$identity) {
            DebugLog::create([
                'class' => VeriffTranslator::class,
                'log' => 'Attempt to save veriff context with unknown identity - ' . json_encode($data) . ' for registry_id - ' . $registry->id,
            ]);

            return;
        }

        $identity->update([
            'idvt_success' => $data['verification']['status'] === 'approved' ? 1 : 0,
            'idvt_result_text' => $data['verification']['status'],
            'idvt_context' => json_encode($data),
            'idvt_completed_at' => Carbon::now(),
            'idvt_identification_number' => $data['verification']['person']['idNumber'] ?? null,
            'idvt_document_type' => $data['verification']['document']['type'] ?? null,
            'idvt_document_number' => $data['verification']['document']['number'] ?? null,
            'idvt_document_country' => $data['verification']['document']['country'] ?? null,
            'idvt_document_valid_until' => $data['verification']['document']['validUntil'] ?? null,
            'idvt_attempt_id' => $data['verification']['attemptId'] ?? null,
            'idvt_context_id' => $data['verification']['id'] ?? null,
            'idvt_document_dob' => $data['verification']['person']['dateOfBirth'] ?? null,
        ]);
    }
}
