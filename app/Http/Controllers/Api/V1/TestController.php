<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ProjectHasCustodian;
use App\Models\CustodianUser;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function test(Request $request)
    {
        return response()->json([
            'message' => 'success',
        ], 200);
    }
}
