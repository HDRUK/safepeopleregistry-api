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
            return $this->OKResponse('active');
        }

        if (Feature::inactive('sponsorship')) {
            return $this->OKResponse('inactive');
        }
        
        return response()->json([
            'message' => 'success',
        ], 200);
    }
}
