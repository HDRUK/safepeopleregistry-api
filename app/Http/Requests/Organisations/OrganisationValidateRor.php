<?php

namespace App\Http\Requests\Organisations;

use App\Http\Requests\BaseFormRequest;

class OrganisationValidateRor extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ror' => [
                'required',
                'string',
                'min:8',
                'max:25',
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
        $this->merge(['ror' => $this->route('ror')]);
    }
}
