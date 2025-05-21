<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ValidationLogComment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Traits\Responses;
use App\Http\Controllers\Controller;
use Exception;

class ValidationLogCommentController extends Controller
{
    use Responses;

    /**
     * @OA\Get(
     *     path="/api/v1/validation_log_comments/{id}",
     *     summary="Get a single validation log comment",
     *     description="Retrieve a specific validation log comment by ID.",
     *     tags={"Validation Log Comments"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the comment",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Comment retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationLogComment")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Comment not found"))
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $comment = ValidationLogComment::with(['user', 'validationLog'])->findOrFail($id);

            if (!$comment) {
                return $this->NotFoundResponse();
            }
            return $this->OKResponse($comment);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }



    /**
     * @OA\Post(
     *     path="/api/v1/validation_log_comments",
     *     summary="Create a new validation log comment",
     *     description="Add a new comment to a validation log.",
     *     tags={"Validation Log Comments"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"validation_log_id", "comment"},
     *             @OA\Property(property="validation_log_id", type="integer", description="ID of the associated validation log"),
     *             @OA\Property(property="comment", type="string", description="Comment text")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Comment created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationLogComment")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'validation_log_id' => 'required|exists:validation_logs,id',
                'user_id' => 'required|exists:users,id',
                'comment' => 'required|string'
            ]);

            $comment = ValidationLogComment::create([
                'validation_log_id' => $validated['validation_log_id'],
                'user_id' => $validated['user_id'],
                'comment' => $validated['comment']
            ]);

            return $this->CreatedResponse($comment);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/validation_log_comments/{id}",
     *     summary="Update a validation log comment",
     *     description="Edit an existing validation log comment.",
     *     tags={"Validation Log Comments"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the comment",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"comment"},
     *             @OA\Property(property="comment", type="string", description="Updated comment text")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Comment updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationLogComment")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Comment not found"))
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'comment' => 'required|string',
            ]);

            $comment = ValidationLogComment::find($id);
            if (!$comment) {
                return $this->NotFoundResponse();
            }

            $comment->update(['comment' => $validated['comment']]);

            return $this->OKResponse($comment);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/validation_log_comments/{id}",
     *     summary="Delete a validation log comment",
     *     description="Remove a comment from the validation logs.",
     *     tags={"Validation Log Comments"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the comment",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Comment deleted successfully",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Comment deleted successfully"))
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Comment not found"))
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $comment = ValidationLogComment::findOrFail($id);
            if (!$comment) {
                return $this->NotFoundResponse();
            }

            $comment->delete();

            return $this->OKResponse(null);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }
}
