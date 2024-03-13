<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\QueryController;

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

// stop all all other routes
Route::any('{path}', function() {
    $response = [
        'message' => 'Resource not found',
    ];

    return response()->json($response)
        ->setStatusCode(404);
});