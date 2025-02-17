<?php

namespace App\Http\Requests\Departments;

use App\Http\Requests\BaseFormRequest;

class CreateDepartment extends BaseFormRequest
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
                'string',
                'required',
                'max:255',
            ],
            'category' => [
                'string',
                'required',
                'max:255',
            ],
        ];
    }
}
