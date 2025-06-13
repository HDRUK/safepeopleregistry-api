<?php


namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\Responses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Models\UserAuditLog;

class UserAuditLogController extends Controller
{
    use Responses;
    public function show(Request $request, int $id): JsonResponse
    {
        $logs = UserAuditLog::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->get(); // paginate?
        return $this->OKResponse($logs);
    }
}
