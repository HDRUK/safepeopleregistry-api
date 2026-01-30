<?php

namespace App\Http\Controllers\Api\V1;

use SendGrid;
use Exception;
use App\Models\EmailLog;
use App\Jobs\SentHtmlEmalJob;
use App\Http\Traits\Responses;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Http\Requests\Admins\UpdateMessageStatus;
use App\Traits\CommonFunctions;

class EmailLogController extends Controller
{
    use CommonFunctions;
    use Responses;

   public function index(Request $request)
    {
        if (!Gate::allows('admin')) {
            return $this->ForbiddenResponse();
        }
        $perPage = $request->integer('per_page', (int)$this->getSystemConfig('PER_PAGE'));

        $logs = EmailLog::searchViaRequest($request->all())
        ->select([
            'id',
            'to',
            'subject',
            'template',
            'job_uuid',
            'job_status',
            'message_id',
            'message_status',
            'message_response',
            'error_message',
            'updated_at'
        ])
        ->when($request->filled('job_status'), function ($q) use ($request) {
            $q->where('job_status', $request->integer('job_status'));
        })
        ->when($request->filled('message_status'), function ($q) use ($request) {
            $q->where('message_status', $request->get('message_status'));
        })        
        ->paginate($perPage);

        return $this->OKResponse($logs);
    }

    public function updateMessageStatus(UpdateMessageStatus $request, int $id)
    {
        if (!Gate::allows('admin')) {
            return $this->ForbiddenResponse();
        }

        try {
            $emailLog = EmailLog::where([
                'id' => $id,
                'job_status' => 1,
            ])->first();

            if (is_null($emailLog)) {
                throw new Exception('No email log found for the id ' . $id);
            }

            $responseSendGrid = SendGrid::checkLogByMessageId($emailLog->message_id);

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
