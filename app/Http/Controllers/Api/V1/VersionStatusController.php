<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class VersionStatusController extends Controller
{
    public function index(Request $request)
    {
        $packagePath = base_path('package.json');

        if (!File::exists($packagePath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'package.json not found'
            ], 500);
        }

        $package = json_decode(File::get($packagePath), true);

        return response()->json([
            'status' => 'ok',
            'app' => config('app.name'),
            'version' => $package['version'] ?? 'unknown',
            'environment' => app()->environment(),
            'timestamp' => now(),
        ]);
    }
}