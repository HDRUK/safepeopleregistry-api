<?php

namespace App\OrcID;

use Config;
use Http;
use Exception;
use App\Models\User;
use App\Jobs\OrcIDScanner;
use App\Models\UserApiToken;

class OrcID
{
    public function getAuthoriseUrl(): string
    {
        $url = Config::get('speedi.system.orcid_auth_url') . 'oauth/authorize?client_id=' .
        Config::get('speedi.system.orcid_app_id') . '&response_type=token&' .
            'scope=openid&redirect_uri=' . Config::get('speedi.system.orcid_redirect_url');

        return $url;
    }

    public function getPublicToken(User $user): string
    {
        // This uses old school curl usage due to newer HTTP
        // fetches not working well with ORCiD API, which
        // returns unauthorised states owing to lacking
        // security context policies. Yet, under curl, it
        // works. Needs further investigation as to the
        // differences.
        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, Config::get('speedi.system.orcid_auth_url') . 'oauth/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                'client_id=' . Config::get('speedi.system.orcid_app_id') . '&' .
                'client_secret=' . Config::get('speedi.system.orcid_client_secret') . '&' .
                'scope=/read-public&' .
                'grant_type=client_credentials'
            );

            $headers = [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
            ];

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                return curl_error($ch);
            }

            curl_close($ch);

            return $result;
        } catch (Exception $e) {
            throw new Exception('Error fetching ORCiD public token: ' . $e->getMessage());
        }
        
    }

    public function storeOrcIDTokenDetails(array $input): bool
    {
        $user = User::where('id', $input['user_id'])->first();
        $token = UserApiToken::where([
            'api_name' => 'orcid',
            'user_id' => $user->id,
        ])->first();

        if (!$token) {
            $token = UserApiToken::create([
                'user_id' => $user->id,
                'api_name' => 'orcid',
                'api_details' => json_encode($input['payload']),
            ]);

            if ($token) {
                OrcIDScanner::dispatch($user);
                return true;
            }

            return false;
        }

        if ($token->update(['api_details' => json_decode($input['payload'])])) {
            OrcIDScanner::dispatch($user);

            return true;
        }

        return false;
    }

    public function getOrcIDRecord(string $token, string $orcid, string $record): array
    {
        $url = Config::get('speedi.system.orcid_public_url') . 'v3.0/'.$orcid.'/'.$record;
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ];

        $response = Http::withHeaders($headers)->get($url);
        if ($response->status() === 200) {
            $response->close();
            return is_array($response->json()) ? $response->json() : [];
        }

        $response->close();
        return [];
    }
}
