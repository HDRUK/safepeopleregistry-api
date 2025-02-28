<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Hdruk\LaravelMjml\Models\EmailTemplate;
use App\Http\Controllers\Controller;

class EmailTemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $emailTemplates = EmailTemplate::all();

        return response()->json([
            'message' => 'success',
            'data' => $emailTemplates,
        ], 200);
    }
}
