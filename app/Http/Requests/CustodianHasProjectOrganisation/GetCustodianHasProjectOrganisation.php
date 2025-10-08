<?php

namespace App\Http\Requests\CustodianHasProjectOrganisation;

use App\Http\Requests\BaseFormRequest;

class GetCustodianHasProjectOrganisation extends BaseFormRequest
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
            'projectOrganisationId' => [
                'required',
                'integer',
                'exists:project_has_organisations,id',
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
        $this->merge(['projectOrganisationId' => $this->route('projectOrganisationId')]);
    }
}
