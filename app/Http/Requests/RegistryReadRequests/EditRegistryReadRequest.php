<?php

namespace App\Http\Requests\RegistryReadRequests;

use App\Http\Requests\BaseFormRequest;

class EditRegistryReadRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'status' => [
                'integer',
                'required',
                'in:1,2',
            ],
            'user_id' => [
                'integer',
                'required',
                'exists:users,id',
            ],
        ];
    }
}
