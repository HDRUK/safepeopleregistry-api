<?php

use App\Http\Controllers\HorizonAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// Horizon dashboard browser login (Keycloak authorization-code flow).
// Kept outside the Horizon path so Horizon's catch-all route can't shadow it.
// See docs/ARCHITECTURE/HORIZON_DASHBOARD_AUTH.md.
Route::prefix('auth/horizon')->controller(HorizonAuthController::class)->group(function () {
    Route::get('login', 'login')->name('horizon.login');
    Route::get('callback', 'callback')->name('horizon.callback');
    Route::get('logout', 'logout')->name('horizon.logout');
});

// stop all all other routes
Route::fallback(function () {
    return response()->json([
        'message' => 'Resource not found',
    ], 404);
});
