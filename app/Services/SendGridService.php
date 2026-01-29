<?php

namespace App\Services;

use Http;
use Exception;

class SendGridService
{
    protected $sgApiHost;
    protected $sgApiKey;

    public function __construct()
    {
        $this->sgApiHost = config('speedi.system.sendgrid_host');
        $this->sgApiKey = config('speedi.system.sendgrid_api_key');

    }

    public function getLongMessageId(string $shortMessageId)
    {
        $urlMessages = $this->sgApiHost . '/v3/messages';
        $query = "msg_id LIKE '{$shortMessageId}%'";

        try {
            $response = Http::withToken(config('services.sendgrid.key', env('SENDGRID_API_KEY')))
                ->acceptJson()
                ->get($urlMessages, [
                    'limit' => 1000,
                    'query' => $query,
                ]);

            $response->throw();

            return $response->json();
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function getLogByLongMessageId(string $longMessageId)
    {
        $urlLogs = $this->sgApiHost . '/v3/logs/' . $longMessageId;

        try {
            $response = Http::withToken($this->sgApiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])->get($urlLogs);

            if (!$response->successful()) {
                throw new Exception('SendGrid Email Logs lookup failed for sg_message_id ' . $longMessageId . ' with status code ' . $response->status());
            }

            return $response->json();
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
