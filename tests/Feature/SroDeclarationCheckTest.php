<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\Organisation;
use App\Models\User;
use App\Rules\Organisations\SroDeclarationCheck;
use App\Services\OrganisationMembershipService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SroDeclarationCheckTest extends TestCase
{
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->organisation = Organisation::factory()->create();
    }

    public function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function createMember(bool $isSro, int $unclaimed = 0): User
    {
        // The observer records when the user became the SRO.
        return $this->withTemporaryObservers(fn () => User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $this->organisation->id,
            'is_sro' => $isSro,
            'is_delegate' => $isSro ? 0 : 1,
            'unclaimed' => $unclaimed,
            'keycloak_id' => $unclaimed ? null : fake()->uuid(),
        ]));
    }

    private function uploadDeclaration(string $status = File::FILE_STATUS_PROCESSED): File
    {
        $file = File::create([
            'name' => 'sro_declaration.pdf',
            // The web app sends types in upper case.
            'type' => strtoupper(File::FILE_TYPE_DECLARATION_SRO),
            'path' => 'sro_declaration.pdf',
            'status' => $status,
        ]);

        $this->organisation->files()->attach($file->id);

        return $file;
    }

    private function passes(): bool
    {
        return (new SroDeclarationCheck())->evaluate(
            Organisation::with(['files', 'claimedSro'])->findOrFail($this->organisation->id),
            ['path' => 'files', 'file_type' => File::FILE_TYPE_DECLARATION_SRO]
        );
    }

    public function test_it_fails_without_a_claimed_sro(): void
    {
        $this->createMember(isSro: true, unclaimed: 1);
        $this->uploadDeclaration();

        $this->assertFalse($this->passes());
    }

    public function test_it_fails_when_the_sro_has_no_declaration(): void
    {
        $this->createMember(isSro: true);

        $this->assertFalse($this->passes());
    }

    public function test_it_passes_when_the_sro_has_uploaded_a_declaration(): void
    {
        $this->createMember(isSro: true);
        Carbon::setTestNow(Carbon::now()->addMinute());
        $this->uploadDeclaration();

        $this->assertTrue($this->passes());
    }

    public function test_it_fails_until_the_declaration_has_been_scanned_clean(): void
    {
        $this->createMember(isSro: true);
        Carbon::setTestNow(Carbon::now()->addMinute());
        $file = $this->uploadDeclaration(File::FILE_STATUS_PENDING);

        $this->assertFalse($this->passes());

        $file->update(['status' => File::FILE_STATUS_FAILED]);
        $this->assertFalse($this->passes());

        $file->update(['status' => File::FILE_STATUS_PROCESSED]);
        $this->assertTrue($this->passes());
    }

    public function test_a_latest_declaration_that_fails_its_scan_fails_it_despite_an_earlier_clean_one(): void
    {
        $this->createMember(isSro: true);
        Carbon::setTestNow(Carbon::now()->addMinute());
        $this->uploadDeclaration();
        Carbon::setTestNow(Carbon::now()->addMinute());
        $this->uploadDeclaration(File::FILE_STATUS_FAILED);

        $this->assertFalse($this->passes());
    }

    public function test_a_latest_declaration_still_being_scanned_fails_it_despite_an_earlier_clean_one(): void
    {
        $this->createMember(isSro: true);
        Carbon::setTestNow(Carbon::now()->addMinute());
        $this->uploadDeclaration();
        Carbon::setTestNow(Carbon::now()->addMinute());
        $latest = $this->uploadDeclaration(File::FILE_STATUS_PENDING);

        $this->assertFalse($this->passes());

        $latest->update(['status' => File::FILE_STATUS_PROCESSED]);
        $this->assertTrue($this->passes());
    }

    public function test_declarations_uploaded_in_the_same_second_are_ordered_by_id(): void
    {
        $this->createMember(isSro: true);
        Carbon::setTestNow(Carbon::now()->addMinute());
        $this->uploadDeclaration();
        $this->uploadDeclaration(File::FILE_STATUS_FAILED);

        $this->assertFalse($this->passes());
    }

    public function test_a_change_of_sro_fails_it_until_a_new_declaration_is_uploaded(): void
    {
        $this->createMember(isSro: true);
        $delegate = $this->createMember(isSro: false);
        Carbon::setTestNow(Carbon::now()->addMinute());
        $this->uploadDeclaration();
        $this->assertTrue($this->passes());

        Carbon::setTestNow(Carbon::now()->addMinute());
        $this->withTemporaryObservers(fn () => (new OrganisationMembershipService())
            ->appointSro($this->organisation, $delegate->id, User::factory()->create()));

        $this->assertFalse($this->passes());

        Carbon::setTestNow(Carbon::now()->addMinute());
        $this->uploadDeclaration();

        $this->assertTrue($this->passes());
    }

    public function test_an_invited_sro_is_counted_as_appointed_when_they_claim(): void
    {
        $invitedSro = $this->createMember(isSro: true, unclaimed: 1);
        $this->assertNull($this->organisation->fresh()->sro_appointed_at);

        Carbon::setTestNow(Carbon::now()->addMinute());
        $this->withTemporaryObservers(function () use ($invitedSro) {
            $invitedSro->unclaimed = false;
            $invitedSro->save();
        });

        $this->assertEquals(now()->startOfSecond(), $this->organisation->fresh()->sro_appointed_at);
        $this->assertEquals($this->organisation->updated_at, $this->organisation->fresh()->updated_at);
    }

    public function test_an_sro_appointed_before_it_was_recorded_keeps_their_existing_declaration(): void
    {
        $this->uploadDeclaration();
        Carbon::setTestNow(Carbon::now()->addMinute());
        $this->createMember(isSro: true);
        $this->organisation->forceFill(['sro_appointed_at' => null])->saveQuietly();

        $this->assertTrue($this->passes());
    }
}
