<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\File;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Illuminate\Support\Facades\Storage;

class FileTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/files';

    public function setUp(): void
    {
        parent::setUp();
        config(['scanning_filesystem_disk' => 'local_scan']);
        Storage::fake('local_scan');

        $this->withUsers();
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

    public function test_cannot_get_file_by_id()
    {
        $latestFile = File::query()->orderBy('id', 'desc')->first();
        $fileIdTest = $latestFile ? $latestFile->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "/{$fileIdTest}"
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_it_should_return_not_download_by_id()
    {
        $latestFile = File::query()->orderBy('id', 'desc')->first();
        $fileIdTest = $latestFile ? $latestFile->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "/{$fileIdTest}/download"
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_it_should_download_processed_file()
    {
        $file = File::create([
            'status' => File::FILE_STATUS_PROCESSED,
            'type' => 'TEST',
            'path' => 'testfile.txt',
            'name' => 'testfile.txt',
        ]);
        Storage::disk('local_scan_scanned')->put('testfile.txt', 'test file content');
        Storage::disk('local_scan_scanned')->assertExists('testfile.txt');

        $fullPath = Storage::disk('local_scan_scanned')->path('testfile.txt');

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
