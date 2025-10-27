<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Laravel\Pennant\Feature;
use App\Http\Traits\Responses;
use App\Http\Controllers\Controller;
use App\Exceptions\NotFoundException;
use App\Models\Feature as FeatureModel;
use App\Http\Requests\Features\GetFeatureById;
use App\Http\Requests\Features\ToggleByFeatureId;

class FeatureController extends Controller
{
    use Responses;

    /**
     * @OA\Get(
     *    path="/api/v1/features",
     *    summary="Return a list of Feature entries",
     *    description="Return a list of Feature entries",
     *    tags={"Feature"},
     *    summary="Feature@index",
     *    security={{"bearerAuth":{}}},
     *
     *    @OA\Response(
     *       response=200,
     *       description="Success",
     *       @OA\JsonContent(
     *          @OA\Property(property="message", type="string"),
     *          @OA\Property(property="data", type="object",
     *            @OA\Property(property="id", type="integer", example="123"),
     *             @OA\Property(property="name", type="string", example="feature"),
     *             @OA\Property(property="scope", type="string", example="\App\Model|User:1"),
     *             @OA\Property(property="value", type="boolean", example="true"),
     *             @OA\Property(property="description", type="string", example="description"),
     *             @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *             @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *          )
     *       ),
     *    ),
     *    @OA\Response(
     *       response=404,
     *       description="Not found response",
     *       @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="not found"),
     *       )
     *    )
     * )
     */
    public function index(Request $request)
    {
        $features =  FeatureModel::all();

        return $this->OKResponse($features);
    }

    /**
     * @OA\Get(
     *    path="/api/v1/features/{featureId}",
     *    summary="Return a Feature entry by its ID",
     *    description="Return a Feature entry by its ID",
     *    tags={"Feature"},
     *    summary="Feature@show",
     *    security={{"bearerAuth":{}}},
     *
     *    @OA\Response(
     *       response=200,
     *       description="Success",
     *       @OA\JsonContent(
     *          @OA\Property(property="message", type="string"),
     *          @OA\Property(property="data", type="object",
     *            @OA\Property(property="id", type="integer", example="123"),
     *             @OA\Property(property="name", type="string", example="feature"),
     *             @OA\Property(property="scope", type="string", example="\App\Model|User:1"),
     *             @OA\Property(property="value", type="boolean", example="true"),
     *             @OA\Property(property="description", type="string", example="description"),
     *             @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *             @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *          )
     *       ),
     *    ),
     *    @OA\Response(
     *       response=400,
     *       description="Invalid argument(s)",
     *       @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *       ),
     *    ),
     *    @OA\Response(
     *       response=404,
     *       description="Not found response",
     *       @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="not found"),
     *       )
     *    )
     * )
     */
    public function show(GetFeatureById $request, int $featureId)
    {
        $feature = FeatureModel::find($featureId);

        if (!$feature) {
            throw new NotFoundException();
        }

        return $this->OKResponse($feature);
    }

    /**
     * @OA\Post(
     *    path="/api/v1/features/{featureId}/toggle",
     *    summary="Toggle and return a Feature entry by its ID",
     *    description="Toggle and return a Feature entry by its ID",
     *    tags={"Feature"},
     *    summary="Feature@show",
     *    security={{"bearerAuth":{}}},
     *
     *    @OA\Response(
     *       response=200,
     *       description="Success",
     *       @OA\JsonContent(
     *          @OA\Property(property="message", type="string"),
     *          @OA\Property(property="data", type="object",
     *            @OA\Property(property="id", type="integer", example="123"),
     *             @OA\Property(property="name", type="string", example="feature"),
     *             @OA\Property(property="scope", type="string", example="\App\Model|User:1"),
     *             @OA\Property(property="value", type="boolean", example="true"),
     *             @OA\Property(property="description", type="string", example="description"),
     *             @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *             @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *          )
     *       ),
     *    ),
     *    @OA\Response(
     *       response=400,
     *       description="Invalid argument(s)",
     *       @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *       ),
     *    ),
     *    @OA\Response(
     *       response=404,
     *       description="Not found response",
     *       @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="not found"),
     *       )
     *    )
     * )
     */
    public function toggleByFeatureId(ToggleByFeatureId $request, int $featureId)
    {
        $feature = FeatureModel::find($featureId);

        if (!$feature) {
            throw new NotFoundException();
        }

        $feature->value = !$feature->value;
        $feature->save();

        Feature::for($feature->scope)->forget($feature->name);

        return $this->OKResponse($feature);
    }
}
