<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Exceptions\NotFoundException;
use App\Models\Department;
use App\Http\Requests\Departments\CreateDepartment;
use App\Http\Requests\Departments\UpdateDepartment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\CommonFunctions;
use App\Http\Controllers\Controller;

class DepartmentController extends Controller
{
    use CommonFunctions;

    public function index(Request $request): JsonResponse
    {
        $deps = Department::paginate((int)$this->getSystemConfig('PER_PAGE'));
        return response()->json([
            'message' => 'success',
            'data' => $deps,
        ], 200);
    }

    public function show(Request $request, int $id): JsonResponse
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

    public function update(UpdateDepartment $request, int $id): JsonResponse
    {
        try {
            $input = $request->only(app(Department::class)->getFillable());

            Department::where('id', $id)->update($input);

            return response()->json([
                'message' => 'success',
                'data' => Department::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
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
