<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\File;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Illuminate\Support\Facades\Storage;

class FileTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/files';

    private $user = null;

    public function setUp(): void
    {
        parent::setUp();
        config(['scanning_filesystem_disk' => 'local_scan']);
        Storage::fake('local_scan');

        $this->user = User::where('id', 1)->first();
    }

    public function test_it_should_return_not_download_if_file_status_is_not_processed()
    {
        $file = File::create([
            'status' => File::FILE_STATUS_PENDING,
            'type' => 'TEST',
            'path' => 'testfile.txt',
            'name' => 'testfile.txt',
        ]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/' . $file->id . '/download'
            );

        $response->assertStatus(404);
    }

    public function test_it_should_download_processed_file()
    {
        $file = File::create([
            'status' => File::FILE_STATUS_PROCESSED,
            'type' => 'TEST',
            'path' => 'testfile.txt',
            'name' => 'testfile.txt',
        ]);
        Storage::disk('local_scan.scanned')->put('testfile.txt', 'test file content');
        Storage::disk('local_scan.scanned')->assertExists('testfile.txt');

        $fullPath = Storage::disk('local_scan.scanned')->path('testfile.txt');

        $this->assertTrue(file_exists($fullPath));

        $response = $this->actingAsKeycloakUser(
            $this->user,
            $this->getMockedKeycloakPayload()
        )
            ->json(
                'GET',
                self::TEST_URL . '/' . $file->id . '/download'
            );

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Expose-Headers', 'Content-Disposition');
        $response->assertHeader('Content-Disposition', 'attachment; filename=testfile.txt');

        $response->assertDownload('testfile.txt');

    }
}
