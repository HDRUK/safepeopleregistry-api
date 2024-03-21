<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/me",
     *      summary="Return currently logged in user",
     *      description="Return currently logged in user",
     *      tags={"User"},
     *      summary="User@me",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="name", type="string", example="User Name"),
     *                  @OA\Property(property="email", type="string", example="user.name@email.com"),
     *                  @OA\Property(property="two_factor_confirmed_at", type="string", example="2024-02-04 12:02:00"),
     *                  @OA\Property(property="registry_id", type="integer", example="1564"),
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorised",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="unauthorised"),
     *          )
     *      )
     * )
     */    
    public function me(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorised',
            ], 401);
        }

        return response()->json([
            'message' => 'success',
            'data' => $user,
        ], 200);
    }
}
