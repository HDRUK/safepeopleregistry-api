<?php

namespace App\Http\Requests\CustodianHasProjectOrganisation;

use App\Http\Requests\BaseFormRequest;

class GetStatus extends BaseFormRequest
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
            'projectId' => [
                'required',
                'integer',
                'exists:projects,id',
            ],
            'organisationId' => [
                'required',
                'integer',
                'exists:organisations,id',
            ],
        ];
    }

    /**
     * Prepare route parameters for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'custodianId' => $this->route('custodianId'),
            'projectId' => $this->route('projectId'),
            'organisationId' => $this->route('organisationId'),
        ]);
    }
}
