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
     * @OA\Get(
     *      path="/api/v1/files/{id}",
     *      summary="Gets an uploaded file",
     *      description="Gets an uploaded file",
     *      tags={"Files"},
     *      summary="Files@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="File ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="File ID",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="users.csv"),
     *                  @OA\Property(property="path", type="string", example="1739297394_users.csv"),
     *                  @OA\Property(property="type", type="string", example="RESEARCHERS_LIST"),
     *                  @OA\Property(property="status", type="string", example="failed")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="file upload failed")
     *          ),
     *      ),
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $file = File::findOrFail($id);

        if ($file) {
            return response()->json([
                'message' => 'success',
                'data' => $file,
            ], 200);
        }

        return response()->json([
            'message' => 'not found',
            'data' => null,
        ], 404);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/files",
     *      summary="Upload a file to the registry",
     *      description="Uploads a file to the registry",
     *      tags={"Files"},
     *      summary="Files@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="File definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="registry_id", type="integer", example="1"),
     *              @OA\Property(property="file", type="file", example=""),
     *              @OA\Property(property="file_type", type="string", example="CV"),
     *              @OA\Property(property="entity_type", type="string", example="researcher"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="file upload failed")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\JsonContent(
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

            if (strtoupper($input['entity_type'] ?? '') === 'RESEARCHER' && isset($input['registry_id']) && $input['registry_id'] !== null) {
                $registryId = intval($input['registry_id']);
                $user = User::where('registry_id', $registryId)->first();

                if (!$user) {
                    throw new Exception('User not found for the given registry ID');
                }

                $registry = Registry::find($user->registry_id);

                if (!$registry) {
                    throw new Exception('Registry not found for the user');
                }

                RegistryHasFile::create([
                    'registry_id' => $registry->id,
                    'file_id' => $fileIn->id,
                ]);
            } elseif (isset($input['organisation_id']) && $input['organisation_id'] !== null) {
                $organisationId = intval($input['organisation_id']);
                $organisation = Organisation::find($organisationId);

                if (!$organisation) {
                    throw new Exception('Organisation not found');
                }

                OrganisationHasFile::create([
                    'organisation_id' => $organisation->id,
                    'file_id' => $fileIn->id,
                ]);
            } else {
                throw new Exception('Invalid or missing registry ID or organisation ID');
            }

            ScanFileUpload::dispatch((int) $fileIn->id, $fileSystem);

            return response()->json([
                'message' => 'success',
                'data' => File::where('id', $fileIn->id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
