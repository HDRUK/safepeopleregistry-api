<?php

namespace App\Http\Requests\ValidationChecks;

use App\Http\Requests\BaseFormRequest;

class GetByCustodianValidationCheck extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'custodianId' => [
                'required',
                'integer',
                'exists:custodians,id',
            ],
        ];
    }

    /**
     * Add Route parameters to the FormRequest.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge(['custodianId' => $this->route('custodianId')]);
    }
}
