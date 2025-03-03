<?php

namespace App\Http\Requests\Gateway;

use App\Http\Requests\BaseFormRequest;

class QueryGatewayDur extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'custodian_id' => [
                'integer',
                'required',
                'exists:custodians,id',
            ],
            'project_id' => [
                'integer',
                'required',
                'exists:projects,id',
            ],
        ];
    }
}
