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
            // No exists rule: the controller returns 404 for a missing ID, as it does for a
            // restricted one, so the response doesn't reveal which restricted IDs exist
            'organisationId' => [
                'required',
                'integer',
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
