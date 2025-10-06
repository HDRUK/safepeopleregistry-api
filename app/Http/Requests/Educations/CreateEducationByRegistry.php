<?php

namespace App\Http\Requests\Educations;

use App\Http\Requests\BaseFormRequest;

class CreateEducationByRegistry extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'registryId' => [
                'required',
                'integer',
                'exists:registries,id',
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
        $this->merge(['registryId' => $this->route('registryId')]);
    }
}
