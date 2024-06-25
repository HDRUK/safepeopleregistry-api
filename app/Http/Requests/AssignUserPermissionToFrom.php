<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignUserPermissionToFrom extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'int',
                'exists:users,id',
            ],
            'issuer_id' => [
                'required',
                'int',
                'exists:issuers,id',
            ],
            'permissions.*' => [
                'required',
                'int',
                'exists:permissions,id',
            ],
        ];
    }
}
