<?php

namespace App\Http\Requests\Custodians;

use App\Http\Requests\BaseFormRequest;

class ConfirmAccessRevoked extends BaseFormRequest
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
            'custodianProjectUserId' => [
                'required',
                'integer',
                'exists:custodian_has_project_has_user,id',
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
            'custodianProjectUserId' => $this->route('custodianProjectUserId'),
        ]);
    }
}
