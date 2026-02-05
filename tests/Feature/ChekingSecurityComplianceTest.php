<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class ChekingSecurityComplianceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    public function test_successfully_processes_checking_security_compliance_cyber_essentials_certified()
    {
        Organisation::query()->update([
            'ce_expiry_date' => now(),
            'ce_plus_expiry_date' => now()->addDays(30),
            'iso_expiry_date' => now()->addDays(30),
            'dsptk_expiry_date' => now()->addDays(30),
        ]);

        $organisations = Organisation::all();

        $command = $this->artisan('app:cheking-security-compliance');
        foreach ($organisations as $item) {
            $command->expectsOutput('checking Cyber Essentials Certified expire for organisation id ' . $item->id . ' :: done');
        }
        $command->assertExitCode(Command::SUCCESS);
    }

    public function test_successfully_processes_checking_security_compliance_cyber_essentials_plus_certified()
    {
        Organisation::query()->update([
            'ce_expiry_date' => now()->addDays(30),
            'ce_plus_expiry_date' => now(),
            'iso_expiry_date' => now()->addDays(30),
            'dsptk_expiry_date' => now()->addDays(30),
        ]);

        $organisations = Organisation::all();

        $command = $this->artisan('app:cheking-security-compliance');
        foreach ($organisations as $item) {
            $command->expectsOutput('checking Cyber Essentials Plus Certified expire for organisation id ' . $item->id . ' :: done');
        }
        $command->assertExitCode(Command::SUCCESS);
    }

    public function test_successfully_processes_checking_security_compliance_isoO27001_accredited()
    {
        Organisation::query()->update([
            'ce_expiry_date' => now()->addDays(30),
            'ce_plus_expiry_date' => now()->addDays(30),
            'iso_expiry_date' => now(),
            'dsptk_expiry_date' => now()->addDays(30),
        ]);

        $organisations = Organisation::all();

        $command = $this->artisan('app:cheking-security-compliance');
        foreach ($organisations as $item) {
            $command->expectsOutput('checking ISO27001 Accredited expire for organisation id ' . $item->id . ' :: done');
        }
        $command->assertExitCode(Command::SUCCESS);
    }

    public function test_successfully_processes_checking_security_compliance_dspt_certified()
    {
        Organisation::query()->update([
            'ce_expiry_date' => now()->addDays(30),
            'ce_plus_expiry_date' => now()->addDays(30),
            'iso_expiry_date' => now()->addDays(30),
            'dsptk_expiry_date' => now(),
        ]);

        $organisations = Organisation::all();

        $command = $this->artisan('app:cheking-security-compliance');
        foreach ($organisations as $item) {
            $command->expectsOutput('checking DSPT Certified expire for organisation id ' . $item->id . ' :: done');
        }
        $command->assertExitCode(Command::SUCCESS);
    }

    public function test_no_notifications_sent_when_no_certifications_expire_today()
    {
        Notification::fake();

        Organisation::query()->update([
            'ce_expiry_date' => now()->addDays(30),
            'ce_plus_expiry_date' => now()->addDays(30),
            'iso_expiry_date' => now()->addDays(30),
            'dsptk_expiry_date' => now()->addDays(30),
        ]);

        $this->artisan('app:cheking-security-compliance')
            ->assertExitCode(Command::SUCCESS);

        Notification::assertNothingSent();
    }
}
