<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ProjectHasCustodian;
use App\Http\Controllers\Controller;
use App\Models\ProjectHasOrganisation;
use App\Models\CustodianHasProjectUser;
use App\Models\CustodianHasProjectOrganisation;
use Laravel\Pennant\Feature;

class TestController extends Controller
{
    public function test(Request $request)
    {
       if (Feature::active('sponsorship')) {
            return response()->json([
                'message' => 'active',
            ], 200);
        }

        if (Feature::inactive('sponsorship')) {
            return response()->json([
                'message' => 'inactive',
            ], 200);
        }
        
        return response()->json([
            'message' => 'success',
        ], 200);
    }
}
