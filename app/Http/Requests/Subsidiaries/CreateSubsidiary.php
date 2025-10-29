<?php

namespace App\Http\Requests\Subsidiaries;

use App\Http\Requests\BaseFormRequest;

class CreateSubsidiary extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'organisationId' => [
                'required',
                'integer',
                'exists:organisations,id',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
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
        $this->merge(['organisationId' => $this->route('organisationId')]);
    }
}
