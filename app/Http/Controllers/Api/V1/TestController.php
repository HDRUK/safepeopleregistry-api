<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    protected $decisionEvaluator = null;

    public function test(Request $request)
    {
        return response()->json([
            'message' => 'success',
        ], 200);
    }
}
