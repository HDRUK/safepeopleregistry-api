<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\ProjectHasOrganisation;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Traits\CommonFunctions;

class ProjectHasOrganisationController extends Controller
{
    use Responses;
    use CommonFunctions;


    public function show(
        Request $request,
        int $projectOrganisationId,
    ) {
        try {
            $phu = ProjectHasOrganisation::with([
                'organisation',
            ])
                ->where([
                    'id' => $projectOrganisationId,
                ])->first();

            return $this->OKResponse($phu);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }
}
