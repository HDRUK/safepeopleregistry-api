<?php

namespace App\Http\Requests\Custodians;

use App\Http\Requests\BaseFormRequest;

class GetStatusAndUser extends BaseFormRequest
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
                'exists:custodians,id',
            ],
            'projectUserId' => [
                'required',
                'integer',
                'exists:project_has_users,id',
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
        $this->merge(['projectUserId' => $this->route('projectUserId')]);
    }
}
