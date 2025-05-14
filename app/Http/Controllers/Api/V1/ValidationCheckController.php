<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\CommonFunctions;
use App\Traits\CheckPermissions;
use App\Http\Traits\Responses;
use App\Models\ValidationCheck;
use Exception;

use App\Enums\ValidationCheckAppliesTo;
use App\Http\Requests\ValidationCheckRequest;
use Illuminate\Validation\Rules\Enum;

class ValidationCheckController extends Controller
{

    use CommonFunctions;
    use CheckPermissions;
    use Responses;


    public function show(ValidationCheck $validationCheck)
    {
        //Gate
        return $this->OKResponse($validationCheck);
    }

    public function store(ValidationCheckRequest $request)
    {
        try {
            $check = ValidationCheck::create($request);
            return $this->CreatedResponse($check);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
