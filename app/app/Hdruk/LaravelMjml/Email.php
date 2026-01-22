<?php

namespace Hdruk\LaravelMjml;

use Illuminate\Mail\Mailable;

class Email extends Mailable
{
    public function __construct(
        public string $jobId,
        public string $userId,
        public $template,
        public array $replacements,
        public ?string $address
) {}

public function build()
{
    return $this
        ->withSymfonyMessage(function ($message) {
            $headers = $message->getHeaders();
            $headers->addTextHeader('X-App-User-Id', $this->userId);
            $headers->addTextHeader('X-App-Job-Id', $this->jobId);
            $headers->addTextHeader('X-App-Mailer', config('mail.default'));
        });
}

}
