<?php

namespace App\Http\Requests\Affiliations;

use App\Http\Requests\BaseFormRequest;

class VerificationEmail extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'verificationCode' => [
                'required',
                'string',
                'exists:affiliations,verification_code',
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
        $this->merge(['verificationCode' => $this->route('verificationCode')]);
    }
}
