<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidationChecks\CreateValidationCheckRequest;
use App\Http\Requests\ValidationChecks\ValidationCheckRequest;
use App\Http\Traits\Responses;
use App\Models\ValidationCheck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValidationCheckController extends Controller
{
    use Responses;

    /**
     * @OA\Get(
     *     path="/api/v1/validation_checks",
     *     summary="List all validation checks",
     *     description="Retrieve all validation checks.",
     *     tags={"Validation Checks"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Validation checks retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ValidationCheck"))
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $checks = ValidationCheck::all();
        return $this->OKResponse($checks);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/validation_checks/{id}",
     *     summary="Get a single validation check",
     *     description="Retrieve a specific validation check by ID.",
     *     tags={"Validation Checks"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the validation check",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Validation check retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationCheck")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Validation check not found",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Validation check not found"))
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        $check = ValidationCheck::find($id);
        if (!$check) {
            return $this->NotFoundResponse();
        }

        return $this->OKResponse($check);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/validation_checks",
     *     summary="Create a new validation check",
     *     description="Create a new validation check entry.",
     *     tags={"Validation Checks"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "description"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Validation check created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationCheck")
     *     )
     * )
     */
    public function store(CreateValidationCheckRequest $request): JsonResponse
    {
        $input = $request->only(app(ValidationCheck::class)->getFillable());

        $check = ValidationCheck::create($input);
        return $this->CreatedResponse($check);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/validation_checks/{id}",
     *     summary="Update a validation check",
     *     description="Edit an existing validation check.",
     *     tags={"Validation Checks"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the validation check",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Validation check updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationCheck")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Validation check not found",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Validation check not found"))
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $input = $request->only(app(ValidationCheck::class)->getFillable());
        $check = ValidationCheck::find($id);
        if (!$check) {
            return $this->NotFoundResponse();
        }

        $check->update($input);

        return $this->OKResponse($check);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/validation_checks/{id}",
     *     summary="Delete a validation check",
     *     description="Remove a validation check.",
     *     tags={"Validation Checks"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the validation check",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Validation check deleted successfully",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Validation check deleted"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Validation check not found",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Validation check not found"))
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $check = ValidationCheck::find($id);
        if (!$check) {
            return $this->NotFoundResponse();
        }

        $check->delete();

        return $this->OKResponse(null);
    }
}
