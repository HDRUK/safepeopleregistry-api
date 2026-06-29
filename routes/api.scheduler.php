<?php

use Illuminate\Support\Facades\Route;

Route::get('/checking_trainings', function (Request $request) {
    Artisan::call('app:checking-trainings');

    return response()->json([
        'message' => 'ok',
    ], 200);
});

Route::get('/checking_security_compliance', function (Request $request) {
    Artisan::call('app:checking-security-compliance');

    return response()->json([
        'message' => 'ok',
    ], 200);
});

Route::get('/checking_end_projects', function (Request $request) {
    Artisan::call('app:checking-projects');

    return response()->json([
        'message' => 'ok',
    ], 200);
});

Route::get('/checking_user_automated_flags', function (Request $request) {
    Artisan::call('app:checking-user-automated-flags');

    return response()->json([
        'message' => 'ok',
    ], 200);
});

Route::get('/checking_organisation_automated_flags', function (Request $request) {
    Artisan::call('app:checking-organisation-automated-flags');

    return response()->json([
        'message' => 'ok',
    ], 200);
});
