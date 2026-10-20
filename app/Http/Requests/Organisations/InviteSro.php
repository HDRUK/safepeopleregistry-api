<?php

namespace App\Http\Requests\Organisations;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class InviteSro extends BaseFormRequest
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
            'first_name' => [
                'required',
                'string',
                'max:255',
            ],
            'last_name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'role' => [
                'required',
                'string',
                'max:255',
            ],
            // Only one of the organisation's own departments.
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('organisation_has_departments', 'department_id')
                    ->where('organisation_id', $this->route('id')),
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
    }
}
