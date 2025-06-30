<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ScanFileUpload;
use App\Models\ONSFile;
use App\Models\File;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="ONSSubmission",
 *     description="API endpoints for managing ONS submissions"
 * )
 */
class ONSSubmissionController extends Controller
{
    use CommonFunctions;

    /**
     * @OA\Post(
     *     path="/api/v1/ons-submissions/csv",
     *     tags={"ONSSubmission"},
     *     summary="Upload a CSV file for ONS submission",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="CSV file to upload"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File uploaded successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="data", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="File upload failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="data", type="string", example="file upload failed - please contact support")
     *         )
     *     )
     * )
     */
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
        $fileSystem = config('speedi.system.scanning_filesystem_disk');
        $storedFilename = time().'_'.$file->getClientOriginalName();
        $path = $file->storeAs(
            '',
            $storedFilename,
            $fileSystem . '_unscanned'
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
            'status' => File::FILE_STATUS_PENDING,
        ]);

        if (!in_array(config('speedi.system.app_env'), ['testing', 'ci'])) {
            ScanFileUpload::dispatchSync($file->id, $fileSystem, 'ONSFile');
        }

        return response()->json([
            'message' => 'success',
            'data' => true,
        ], 200);
    }
}
