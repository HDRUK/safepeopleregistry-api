<?php

namespace App\Http\Controllers\Api\V1;

use SendGrid;
use Exception;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admins\UpdateMessageStatus;

class EmailLogController extends Controller
{
    use Responses;

    public function updateMessageStatus(UpdateMessageStatus $request, int $id)
    {
        try {
            $emailLog = EmailLog::where([
                'id' => $id,
                'job_status' => 1,
            ])->first();

            if (is_null($emailLog)) {
                throw new Exception('No email log found for the id ' . $id);
            }

            // $responseSendGrid = SendGrid::checkLogByMessageId($id);

            // we need some logic here

            // return $this->OKResponse($responseSendGrid);
            return $this->NotImplementedResponse();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
