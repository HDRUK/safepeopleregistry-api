<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Traits\Responses;
use App\Traits\ProjectUsersStateWorkflow;
use App\Models\Project;
use App\Models\Registry;
use App\Models\State;
use App\Models\ProjectHasUser;
use App\Traits\CommonFunctions;
use App\Traits\FilterManager;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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
