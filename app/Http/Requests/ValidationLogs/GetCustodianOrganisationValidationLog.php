<?php

namespace App\Http\Requests\ValidationLogs;

use App\Http\Requests\BaseFormRequest;

class GetCustodianOrganisationValidationLog extends BaseFormRequest
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
            'organisationId' => [
                'required',
                'integer',
                'exists:organisations,id',
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
        $this->merge(['organisationId' => $this->route('organisationId')]);
    }
}
