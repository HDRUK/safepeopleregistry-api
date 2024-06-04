<?php

namespace App\Jobs;

use Exception;

use App\Models\File;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ScanFileUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private int $fileId = 0;
    private string $fileSystem = '';

    /**
     * Create a new job instance.
     */
    public function __construct(int $fileId, string $fileSystem)
    {
        $this->fileId = $fileId;
        $this->fileSystem = $fileSystem;
    }

    /**
     * Execute the job.
     * 
     * @return void
     */
    public function handle(): void
    {
        $file = File::findOrFail($this->fileId);
        $filePath = $file->path;

        $body = [
            'file' => (string) $filePath, 
            'storage' => (string) $this->fileSystem
        ];
        $url = env('CLAMAV_API_URL', 'http://clamav:3001') . '/scan_file';
        
        $response = Http::post(
            $url,
            ['file' => $filePath, 'storage' => $this->fileSystem]
        );
        $isInfected = $response['isInfected'];

        // Check if the file is infected
        if ($isInfected) {
            $file->update([
                'status' => 'FAILED',
            ]);
            Storage::disk($this->fileSystem . '.unscanned')
                ->delete($file->path);
        } else {
            $loc = $file->path;
            $content = Storage::disk($this->fileSystem . '.unscanned')->get($loc);
            Storage::disk($this->fileSystem . '.scanned')->put($loc, $content);
            Storage::disk($this->fileSystem . '.unscanned')->delete($loc);

            $file->update([
                'status' => 'PROCESSED',
                'path' => $loc
            ]);
        }
    }

}