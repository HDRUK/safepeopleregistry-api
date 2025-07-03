<?php

namespace App\Gateway;

use Http;
use Exception;
use App\Models\Custodian;
use App\Models\Project;

class Gateway
{
    public function getDataUsesByProjectID(int $custodianId, int $projectId): array
    {
        try {
            $durUrl = config('speedi.system.gateway_api_url') . '/dur?project_id=';

            $custodian = Custodian::where('id', $custodianId)->first();
            $project = Project::where('id', $projectId)->first();

            if (!$project || !$custodian) {
                return [];
            }

            $dur = Http::withHeaders([
                'x-application-id' => $custodian->gateway_app_id,
                'x-client-id' => $custodian->gateway_client_id,
            ])->get(
                $durUrl . $project->unique_id
            );

            $payload = $dur->json()['data'] ?? [];
            $dur->close();

            return $payload;
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
