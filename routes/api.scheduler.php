<?php

use Illuminate\Support\Facades\Route;

Route::get('/checking_certificates', function (Request $request) {
    Artisan::call('app:checking-certificates');

    return response()->json([
        'message' => 'ok',
    ], 200);
});
