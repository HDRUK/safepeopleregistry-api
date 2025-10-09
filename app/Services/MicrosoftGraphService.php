<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Hdruk\LaravelMjml\Email;

class MicrosoftGraphService
{
    protected $client;
    protected $tenantId;
    protected $clientId;
    protected $clientSecret;
    protected $sender;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 10]);
        $this->tenantId = config('mail.mailers.exchange.tenant_id');
        $this->clientId = config('mail.mailers.exchange.client_id');
        $this->clientSecret = config('mail.mailers.exchange.client_secret');
        $this->sender = config('mail.mailers.exchange.sender');
    }

    protected function getAccessToken()
    {
        $response = $this->client->post("https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token", [
            'form_params' => [
                'client_id' => $this->clientId,
                'scope' => 'https://graph.microsoft.com/.default',
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['access_token'];
    }

    public function sendMail(array $to, Email $email)
    {
        $token = $this->getAccessToken();

        // Firstly, build the email content
        $email->build();

        $reflection = new \ReflectionClass($email);
        $property = $reflection->getProperty('html');
        $property->setAccessible(true);

        $htmlContent = $property->getValue($email);

        $response = $this->client->post('https://graph.microsoft.com/v1.0/users/' . $this->sender . '/sendMail', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'message' => [
                    'subject' => $email->getSubject(),
                    'body' => [
                        'contentType' => 'HTML',
                        'content' => $htmlContent,
                    ],
                    'toRecipients' => [
                        [
                            'emailAddress' => [
                                'address' => $to['email'],
                            ],
                        ],
                    ]
                ],
                'saveToSentItems' => 'true',
            ],
        ]);

        if ($response->getStatusCode() === 202) {
            return true;
        }

        Log::error('Microsoft Graph API sendMail failed', [
            'body' => (string) $response->getBody(),
            'status' => (string) $response->getStatusCode(),
        ]);

        return false;
    }
}
