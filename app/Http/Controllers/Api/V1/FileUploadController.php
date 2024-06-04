<?php

namespace App\Http\Controllers\Api\V1;

use Http;
use Storage;
use Exception;

use App\Models\File;
use App\Models\Registry;
use App\Models\SystemConfig;
use App\Models\RegistryHasFile;

use App\Jobs\ScanFileUpload;

use Illuminate\Http\Request;
Use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Facade;

use App\Http\Controllers\Controller;

class FileUploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $input = $request->all();

        $maxFilesize = SystemConfig::where('name', 'MAX_FILESIZE')->first()->value;
        $supportedTypes = SystemConfig::where('name', 'SUPPORTED_FILETYPES')->first()->value;
        
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
                'data' => 'file upload failed',
            ], 400);
        }

        $file = File::create([
            'name' => $storedFilename,
            'type' => $input['file_type'],
            'path' => $path,
            'status' => 'PENDING',
        ]);

        $registry = Registry::where('user_id', $request->user()->id)->first();

        RegistryHasFile::create([
            'registry_id' => $registry->id,
            'file_id' => $file->id,
        ]);

        ScanFileUpload::dispatch((int)$file->id, $fileSystem);

        return response()->json([
            'message' => 'success',
            'data' => $file->id,
        ], 200);
    }
}
