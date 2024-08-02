<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ScanFileUpload;
use App\Models\ONSFile;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ONSSubmissionController extends Controller
{
    use CommonFunctions;

    public function receiveCSV(Request $request): JsonResponse
    {
        $input = $request->all();

        $maxFilesize = $this->getSystemConfig('MAX_FILESIZE');
        $supportedTypes = 'csv';

        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:'.$supportedTypes,
                'max:'.(int) $maxFilesize * 1000,
            ],
        ]);

        $file = $request->file('file');
        $fileSystem = env('SCANNING_FILESYSTEM_DISK', 'local_scan');
        $storedFilename = time().'_'.$file->getClientOriginalName();
        $path = $file->storeAs(
            '',
            $storedFilename,
            $fileSystem.'.unscanned'
        );

        if (! $path) {
            return response()->json([
                'message' => 'failed',
                'data' => 'file upload failed - please contact support',
            ], 400);
        }

        $file = ONSFile::create([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'status' => 'PENDING',
        ]);

        ScanFileUpload::dispatch($file->id, $fileSystem, 'ONSFile');

        return response()->json([
            'message' => 'success',
            'data' => true,
        ], 200);
    }
}
