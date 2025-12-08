<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CustodianHasProjectOrganisation;
use App\Models\ProjectHasOrganisation;
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
