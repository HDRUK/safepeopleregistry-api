<?php

namespace Tests\Feature;

use Database\Seeders\EmailTemplatesSeeder;
use Hdruk\LaravelMjml\Models\EmailTemplate;
use Tests\TestCase;

class DelegateRemovedEmailTemplatesTest extends TestCase
{
    private const TEMPLATES = [
        'delegate_removed_user' => [
            '[[organisation.organisation_name]]',
            '[[delegate_first_name]]',
            '[[actor_first_name]]',
            '[[actor_last_name]]',
        ],
        'delegate_removed_organisation' => [
            '[[organisation.organisation_name]]',
            '[[recipient_first_name]]',
            '[[delegate_first_name]]',
            '[[delegate_last_name]]',
            '[[actor_first_name]]',
            '[[actor_last_name]]',
        ],
    ];

    public function test_the_seeder_creates_the_delegate_removed_templates(): void
    {
        EmailTemplate::whereIn('identifier', array_keys(self::TEMPLATES))->forceDelete();

        $this->seed(EmailTemplatesSeeder::class);

        foreach (self::TEMPLATES as $identifier => $placeholders) {
            $template = EmailTemplate::where('identifier', $identifier)->first();

            $this->assertNotNull($template, "{$identifier} was not created");

            // EmailTemplate declares public $subject/$body properties that shadow the attributes,
            // so read them via array access as SendEmailJob does.
            foreach ($placeholders as $placeholder) {
                $this->assertStringContainsString($placeholder, $template['subject'] . $template['body']);
            }
        }
    }
}
