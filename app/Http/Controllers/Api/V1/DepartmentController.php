<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Exceptions\NotFoundException;
use App\Http\Requests\Departments\GetDepartment;
use App\Http\Requests\Departments\CreateDepartment;
use App\Http\Requests\Departments\DeleteDepartment;
use App\Http\Requests\Departments\UpdateDepartment;

/**
 * @OA\Tag(
 *     name="Department",
 *     description="API endpoints for managing departments"
 * )
 */
class DepartmentController extends Controller
{
    use CommonFunctions;

    /**
     * @OA\Get(
     *     path="/api/v1/departments",
     *     tags={"Department"},
     *     summary="Get a list of departments",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Department")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $deps = Department::paginate((int)$this->getSystemConfig('PER_PAGE'));
        return response()->json([
            'message' => 'success',
            'data' => $deps,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/departments/{id}",
     *     tags={"Department"},
     *     summary="Get a specific department by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the department",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Department")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid argument(s)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Department not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Department not found")
     *         )
     *     )
     * )
     */
    public function show(GetDepartment $request, int $id): JsonResponse
    {
        $dep = Department::findOrFail($id);
        if ($dep) {
            return response()->json([
                'message' => 'success',
                'data' => $dep,
            ], 200);
        }

        throw new NotFoundException();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/departments",
     *     tags={"Department"},
     *     summary="Create a new department",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Department")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="data", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Validation error")
     *         )
     *     )
     * )
     */
    public function store(CreateDepartment $request): JsonResponse
    {
        try {
            $input = $request->only(app(Department::class)->getFillable());

            $dep = Department::create([
                'name' => $input['name'],
                'category' => $input['category'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $dep->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/departments/{id}",
     *     tags={"Department"},
     *     summary="Update an existing department",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the department",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Department")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated",
     *         @OA\JsonContent(ref="#/components/schemas/Department")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid argument(s)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Department not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Department not found")
     *         )
     *     )
     * )
     */
    public function update(UpdateDepartment $request, int $id): JsonResponse
    {
        try {
            $input = $request->only(app(Department::class)->getFillable());

            $dep = Department::findOrFail($id);
            $dep->update($input);

            return response()->json([
                'message' => 'success',
                'data' => Department::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/departments/{id}",
     *     tags={"Department"},
     *     summary="Delete a department",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the department",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Deleted",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid argument(s)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Department not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Department not found")
     *         )
     *     )
     * )
     */
    public function destroy(DeleteDepartment $request, int $id): JsonResponse
    {
        try {
            Department::where('id', $id)->delete();
            return response()->json([
                'message' => 'success',
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
