<?php

namespace App\Http\Requests\ValidationLogComments;

use App\Http\Requests\BaseFormRequest;

class UpdateValidationLogComment extends BaseFormRequest
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
                'exists:validation_log_comments,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'id.exists' => 'Comment not found',
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
    }
}
