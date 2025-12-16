<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\CustodianUser;
use App\Http\Controllers\Controller;
use App\Models\CustodianHasProjectUser;

class TestController extends Controller
{
    public function test(Request $request)
    {

        return response()->json([
            'message' => 'success',
        ], 200);
    }
}
