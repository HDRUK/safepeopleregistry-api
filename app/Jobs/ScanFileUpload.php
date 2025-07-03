<?php

namespace App\Jobs;

use App\Models\File;
use App\Traits\CommonFunctions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ScanFileUpload implements ShouldQueue
{
    use CommonFunctions;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private int $fileId = 0;

    private string $fileSystem = '';

    private string $modelType = 'File';

    /**
     * Create a new job instance.
     */
    public function __construct(int $fileId, string $fileSystem, string $modelType = 'File')
    {
        $this->fileId = $fileId;
        $this->fileSystem = $fileSystem;
        $this->modelType = $modelType;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $model = $this->mapModelFromString($this->modelType);
        $file = $model::findOrFail($this->fileId);
        $filePath = $file->path;

        $body = [
            'file' => (string) $filePath,
            'storage' => (string) $this->fileSystem,
        ];
        $url = config('speedi.system.clam_av_service_url') . '/scan_file';

        $response = Http::post(
            $url,
            [
                'file' => $filePath,
                'storage' => $this->fileSystem,
                'service_path' => config('speedi.system.app_url'),
            ]
        );

        $isInfected = $response['isInfected'] ?? null;
        $response->close();

        // Check if the file is infected
        if ($isInfected || $isInfected === null) {
            $file->update([
                'status' => File::FILE_STATUS_FAILED,
            ]);
            Storage::disk($this->fileSystem . '_unscanned')
                ->delete($file->path);
        } else {
            $loc = $file->path;
            $content = Storage::disk($this->fileSystem . '_unscanned')->get($loc);

            Storage::disk($this->fileSystem . '_scanned')->put($loc, $content);
            Storage::disk($this->fileSystem . '_unscanned')->delete($loc);

            $file->update([
                'status' => File::FILE_STATUS_PROCESSED,
                'path' => $loc,
            ]);
        }
    }

    public function failed()
    {
        $model = $this->mapModelFromString($this->modelType);
        $file = $model::findOrFail($this->fileId);

        $file->update([
            'status' => File::FILE_STATUS_FAILED,
        ]);
    }
}
