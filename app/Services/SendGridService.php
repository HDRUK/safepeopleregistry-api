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

    public function checkLogByMessageId(string $messageId)
    {
        $urlLogs = $this->sgApiHost . '/v3/logs/' . $messageId;

        try {
            $response = Http::withToken($this->sgApiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])->get($urlLogs);

            if (!$response->successful()) {
                throw new Exception('SendGrid Email Logs lookup failed for sg_message_id ' . $messageId . ' with status code ' . $response->status());
            }

            return $response->json();
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
