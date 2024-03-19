<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\QueryController;
use App\Http\Controllers\Api\V1\TrainingController;
use App\Http\Controllers\Api\V1\IssuerController;
use App\Http\Controllers\Api\V1\EndorsementController;
use App\Http\Controllers\Api\V1\ProjectController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Debug endpoint
Route::post('v1/query', [QueryController::class, 'query']);

// Debug Auth routes
Route::middleware('api')->post('v1/login', [AuthController::class, 'login']);
Route::middleware('api')->post('v1/logout', [AuthController::class, 'logout']);
Route::middleware('api')->post('v1/refresh', [AuthController::class, 'refresh']);
Route::middleware('api')->post('v1/me', [AuthController::class, 'me']);

Route::middleware('api')->get('v1/training', [TrainingController::class, 'index']);
Route::middleware('api')->get('v1/training/{id}', [TrainingController::class, 'show']);
Route::middleware('api')->post('v1/training', [TrainingController::class, 'store']);
Route::middleware('api')->put('v1/training/{id}', [TrainingController::class, 'update']);
Route::middleware('api')->patch('v1/training/{id}', [TrainingController::class, 'edit']);
Route::middleware('api')->delete('v1/training/{id}', [TrainingController::class, 'destroy']);

Route::middleware('api')->get('v1/issuers', [IssuerController::class, 'index']);
Route::middleware('api')->get('v1/issuers/{id}', [IssuerController::class, 'show']);
Route::middleware('api')->post('v1/issuers', [IssuerController::class, 'store']);
Route::middleware('api')->put('v1/issuers/{id}', [IssuerController::class, 'update']);
Route::middleware('api')->patch('v1/issuers/{id}', [IssuerController::class, 'edit']);
Route::middleware('api')->delete('v1/issuers/{id}', [IssuerController::class, 'destroy']);

Route::middleware('api')->get('v1/endorsements', [EndorsementController::class, 'index']);
Route::middleware('api')->get('v1/endorsements/{id}', [EndorsementController::class, 'show']);
Route::middleware('api')->post('v1/endorsements', [EndorsementController::class, 'store']);

Route::middleware('api')->get('v1/projects', [ProjectController::class, 'index']);
Route::middleware('api')->get('v1/projects/{id}', [ProjectController::class, 'show']);
Route::middleware('api')->post('v1/projects', [ProjectController::class, 'store']);
Route::middleware('api')->put('v1/projects/{id}', [ProjectController::class, 'update']);
Route::middleware('api')->patch('v1/projects/{id}', [ProjectController::class, 'edit']);
Route::middleware('api')->delete('v1/projects/{id}', [ProjectController::class, 'destroy']);

// stop all all other routes
Route::any('{path}', function() {
    $response = [
        'message' => 'Resource not found',
    ];

    return response()->json($response)
        ->setStatusCode(404);
});