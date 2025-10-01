<?php

namespace App\Http\Requests\Custodians;

use App\Http\Requests\BaseFormRequest;

class GetCustodianByUniqueIdentifier extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'uniqueIdentifier' => [
                'required',
                'string',
                'exists:custodians,unique_identifier',
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
        $this->merge(['uniqueIdentifier' => $this->route('uniqueIdentifier')]);
    }
}
