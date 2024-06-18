<?php

namespace App\OrcID;

use Http;
use Exception;

use App\Models\User;
use App\Models\Registry;
use App\Models\RegistryHasOrganisation;

use Illuminate\Http\JsonResponse;

class OrcID {
    public function getAuthoriseUrl(): string
    {
        $url = env('ORCID_URL') . 'oauth/authorize?client_id=' .
            env('ORCID_APP_ID') . '&response_type=code&' .
            'scope=/authenticate&redirect_uri=https://8d25-109-224-227-234.ngrok-free.app';

        return $url;
    }

    public function token(): string
    {
        $payload = [
            'client_id' => env('ORCID_APP_ID'),
            'client_secret' => env('ORCID_CLIENT_SECRET'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'https://8d25-109-224-227-234.ngrok-free.app',
            'code' => '9U_vOr',
            'scope' => '/read-public',
        ];

        $url = env('ORCID_URL') . 'oauth/token';

        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post($url, $payload);

        dd($response->json());
    }

    public function request(): array
    {

    }
}
