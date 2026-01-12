<?php

use Illuminate\Support\Facades\Route;

Route::get('/checking_certificates', function (Request $request) {
    Artisan::call('app:checking-certificates');

    return response()->json([
        'message' => 'ok',
    ], 200);
});

Route::get('/cheking_security_compliance', function (Request $request) {
    Artisan::call('app:cheking-security-compliance');

    return response()->json([
        'message' => 'ok',
    ], 200);
});

Route::get('/cheking_end_projects', function (Request $request) {
    Artisan::call('app:checking-projects');

    return response()->json([
        'message' => 'ok',
    ], 200);
});

Route::get('/cheking_user_automated_flags', function (Request $request) {
    Artisan::call('aapp:checking-user-automated-flags');

    return response()->json([
        'message' => 'ok',
    ], 200);
});

Route::get('/cheking_organisation_automated_flags', function (Request $request) {
    Artisan::call('app:checking-organisation-automated-flags');

    return response()->json([
        'message' => 'ok',
    ], 200);
});
