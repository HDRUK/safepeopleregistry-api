<?php

namespace App\Http\Controllers;

use Exception;

use App\Models\Registry;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Controllers\Controller;
use App\Exception\NotFoundException;

class RegistryController extends Controller
{
    // Swagger TBC
    public function index(Request $request): JsonResponse
    {
        $registries = Registry::all();

        return $response->json(
            $registries
        );
    }

    // Swagger TBC
    public function show(Request $request, int $id): JsonResponse
    {
        $registries = Registry::findOrFail($id);
        if ($registries) {
            return response()->json([
                'message' => 'success',
                'data' => $registries,
            ], 200);
        }

        throw new NotFoundException();
    }

    // Swagger TBC
    public function store(Request $request): JsonResponse
    {
        try {
            $input = $request->all();

            $registry = Registry::create([
                'user_id' => $input['user_id'],
                'dl_ident' => $input['dl_ident'],
                'pp_ident' => $input['pp_ident'],
                'verified' => $input['verified'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $registry->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    // Swagger TBC
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $input = $request->all();

            Registry::where('id', $id)->update([
                'user_id' => $input['user_id'],
                'dl_ident' => $input['dl_ident'],
                'pp_ident' => $input['pp_ident'],
                'verified' => $input['verified'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => Registry::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    // Swagger TBC
    public function edit(Request $request, int $id): JsonResponse
    {
        try {
            $input = $request->all();

            Registry::where('id', $id)->update([
                'user_id' => $input['user_id'],
                'dl_ident' => $input['dl_ident'],
                'pp_ident' => $input['pp_ident'],
                'verified' => $input['verified'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => Registry::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    // Swagger TBC
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            Registry::where('id', $id)->delete();

            return response()->json([
                'message' => 'success',
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
