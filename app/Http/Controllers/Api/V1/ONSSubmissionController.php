<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ONSFile;

use App\Jobs\ScanFileUpload;

use Illuminate\Http\Request;
Use Illuminate\Http\JsonResponse;

use App\Http\Controllers\Controller;

use App\Traits\CommonFunctions;

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
                'mimes:' . $supportedTypes,
                'max:' . (int)$maxFilesize * 1000,
            ],
        ]);

        $file = $request->file('file');
        $fileSystem = env('SCANNING_FILESYSTEM_DISK', 'local_scan');
        $storedFilename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs(
            '', $storedFilename, $fileSystem . '.unscanned'
        );

        if (!$path) {
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
