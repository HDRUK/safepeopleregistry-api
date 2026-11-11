<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Thrown when a Custodian User would end up holding more than one role
 * (see CustodianUser::ROLE_PERMISSIONS). Roles are mutually exclusive.
 */
class CustodianRoleConflictException extends Exception
{
    public const MESSAGE = 'A custodian user can only have one role: CUSTODIAN_ADMIN, CUSTODIAN_APPROVER or CUSTODIAN_READ_ONLY';

    public function __construct(string $message = self::MESSAGE, ?Exception $previous = null)
    {
        parent::__construct($message, Response::HTTP_UNPROCESSABLE_ENTITY, $previous);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
