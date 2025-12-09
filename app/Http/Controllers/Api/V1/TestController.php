<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ProjectHasCustodian;
use App\Http\Controllers\Controller;
use App\Models\ProjectHasOrganisation;
use App\Models\CustodianHasProjectUser;
use App\Models\CustodianHasProjectOrganisation;

class TestController extends Controller
{
    public function test(Request $request)
    {
        return response()->json([
            'message' => 'success',
        ], 200);
    }
}
