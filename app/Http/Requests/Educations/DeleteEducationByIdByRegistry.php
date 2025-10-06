<?php

namespace App\Http\Requests\Educations;

use App\Http\Requests\BaseFormRequest;

class DeleteEducationByIdByRegistry extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                'exists:educations,id',
            ],
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
        $this->merge(['id' => $this->route('id')]);
        $this->merge(['registryId' => $this->route('registryId')]);
    }
}
