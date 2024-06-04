<?php

namespace App\Http\Controllers\Api\V1;

use Exception;

use App\Models\File;
use App\Models\Registry;
use App\Models\SystemConfig;
use App\Models\RegistryHasFile;

use Illuminate\Http\Request;
Use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Storage;

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
        $path = $file->store('/users/' . $request->user()->id . '/documents');

        if (!$path) {
            return response()->json([
                'message' => 'failed',
                'data' => 'file upload failed',
            ], 400);
        }

        $fileParts = explode('/', $path);

        $file = File::create([
            'name' => $fileParts[count($fileParts)-1],
            'type' => $input['file_type'],
            'path' => $path,
        ]);

        $registry = Registry::where('user_id', $request->user()->id)->first();

        RegistryHasFile::create([
            'registry_id' => $registry->id,
            'file_id' => $file->id,
        ]);

        return response()->json([
            'message' => 'success',
            'data' => $file->id,
        ]);
    }
}
