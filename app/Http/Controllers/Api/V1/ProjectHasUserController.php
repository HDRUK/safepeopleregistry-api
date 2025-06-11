<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\Responses;
use App\Traits\ProjectUsersStateWorkflow;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectHasUserController extends Controller
{
    use CommonFunctions;
    use Responses;
    use ProjectUsersStateWorkflow;

    public function getWorkflow(Request $request): JsonResponse
    {
        return $this->OKResponse($this->transitions);
    }
}
