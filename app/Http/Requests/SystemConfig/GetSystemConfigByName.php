<?php

namespace App\Http\Requests\SystemConfig;

use App\Http\Requests\BaseFormRequest;

class GetSystemConfigByName extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'integer',
                'exists:system_config,name',
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
        $this->merge(['name' => $this->route('name')]);
    }
}
