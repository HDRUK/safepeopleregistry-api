<?php

namespace App\Http\Requests\CustodianModelConfig;

use App\Http\Requests\BaseFormRequest;

class UpdateCustodianModelConfigRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'active' => [
                'boolean',
                'sometimes',
            ],
        ];
    }
}
