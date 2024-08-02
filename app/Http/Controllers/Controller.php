<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(title="SPEEDI-AS API", version="0.1")
 */
class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;
}
