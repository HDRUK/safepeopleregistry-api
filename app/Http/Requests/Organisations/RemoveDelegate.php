<?php

namespace App\Http\Requests\Organisations;

use App\Http\Requests\BaseFormRequest;

class RemoveDelegate extends BaseFormRequest
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
                'exists:organisations,id',
            ],
            'userId' => [
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
        $this->merge([
            'id' => $this->route('id'),
            'userId' => $this->route('userId'),
        ]);
    }
}
