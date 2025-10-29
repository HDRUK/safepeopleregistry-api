<?php

namespace App\Http\Requests\Subsidiaries;

use App\Http\Requests\BaseFormRequest;

class DeleteSubsidiary extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subsidiaryId' => [
                'required',
                'integer',
                'exists:subsidiaries,id',
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
        $this->merge(['subsidiaryId' => $this->route('subsidiaryId')]);
        $this->merge(['organisationId' => $this->route('organisationId')]);
    }
}
