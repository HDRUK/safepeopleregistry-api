<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ScanFileUpload;
use App\Models\File;
use App\Models\Organisation;
use App\Models\OrganisationHasFile;
use App\Models\Registry;
use App\Models\RegistryHasFile;
use App\Models\User;
use App\Traits\CommonFunctions;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    use CommonFunctions;

    /**
     * @OA\Post(
     *      path="/api/v1/files",
     *      summary="Upload a file to the registry",
     *      description="Uploads a file to the registry",
     *      tags={"Files"},
     *      summary="Files@store",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="File definition",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="file", type="file", example=""),
     *              @OA\Property(property="file_type", type="string", example="CV"),
     *              @OA\Property(property="entity_type", type="string", example="researcher"),
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad request",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string", example="file upload failed")
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="integer", example="1"),
     *          ),
     *      )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $input = $request->all();

            $maxFilesize = $this->getSystemConfig('MAX_FILESIZE');
            $supportedTypes = $this->getSystemConfig('SUPPORTED_FILETYPES');

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

            if (!$path) {
                return response()->json([
                    'message' => 'failed',
                    'data' => 'file upload failed',
                ], 400);
            }

            $fileIn = File::create([
                'name' => $file->getClientOriginalName(),
                'type' => $input['file_type'],
                'path' => $path,
                'status' => 'PENDING',
            ]);

            if (strtoupper($input['entity_type']) === 'RESEARCHER') {

                $user = User::where('id', $request->user()->id)->first();
                $registry = Registry::where('id', $user->registry_id)->first();

                RegistryHasFile::create([
                    'registry_id' => $registry->id,
                    'file_id' => $fileIn->id,
                ]);
            } else {
                $organisation = Organisation::where('id', $input['organisation_id'])->first();
                // Organisation
                OrganisationHasFile::create([
                    'organisation_id' => $organisation->id,
                    'file_id' => $fileIn->id,
                ]);
            }

            ScanFileUpload::dispatch((int) $fileIn->id, $fileSystem);

            return response()->json([
                'message' => 'success',
                'data' => $fileIn->id,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
