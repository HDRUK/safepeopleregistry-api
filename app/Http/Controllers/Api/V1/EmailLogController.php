<?php

namespace App\Http\Controllers\Api\V1;

use SendGridService;
use Exception;
use App\Models\EmailLog;
use App\Jobs\SentHtmlEmalJob;
use App\Http\Traits\Responses;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Admins\UpdateMessageStatus;

class EmailLogController extends Controller
{
    use Responses;

    public function updateMessageStatus(UpdateMessageStatus $request, int $id)
    {
        if (!Gate::allows('admin')) {
            return $this->ForbiddenResponse();
        }

        try {
            $emailLog = EmailLog::where([
                'id' => $id,
            ])->first();

            if (is_null($emailLog)) {
                throw new Exception('No email log found for the id ' . $id);
            }

            // long message id lookup
            $getLongMessageId = SendGridService::getLongMessageId($emailLog->message_id);
            $longMessageId = $getLongMessageId['messages'][0]['msg_id'] ?? null;

            if (is_null($longMessageId)) {
                throw new Exception('No long message id found for short message id ' . $emailLog->message_id);
            }

            $responseSendGrid = SendGridService::getLogByLongMessageId($longMessageId);

            if ($responseSendGrid['status'] === EmailLog::EMAIL_STATUS_DELIVERED) {
                $emailLog->job_status = 1;
                $emailLog->message_status = $responseSendGrid['status'];
                $emailLog->message_response = json_encode($responseSendGrid);
                $emailLog->save();
            } elseif (in_array($responseSendGrid['status'], [
                EmailLog::EMAIL_STATUS_PROCESSED,
                EmailLog::EMAIL_STATUS_DEFERRED,
            ])) {
                $emailLog->message_status = $responseSendGrid['status'];
                $emailLog->message_response = json_encode($responseSendGrid);
                $emailLog->save();
            } else {
                $emailLog->job_status = 0;
                $emailLog->message_status = $responseSendGrid['status'];
                $emailLog->message_response = json_encode($responseSendGrid);
                $emailLog->save();
                throw new Exception('Email status ' . $responseSendGrid['status'] . ' not handled for email log id ' . $id);
            }

            return $this->OKResponse($responseSendGrid);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function resendEmail(UpdateMessageStatus $request, int $id)
    {
        if (!Gate::allows('admin')) {
            return $this->ForbiddenResponse();
        }

        try {
            SentHtmlEmalJob::dispatch($id);

            return $this->OKResponse('Resend email job dispatched for email log id ' . $id);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
