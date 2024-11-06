<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseFormRequest;

class CreateUser extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => [
                'string',
                'required',
            ],
            'last_name' => [
                'string',
                'required',
            ],
            'email' => [
                'email',
                'required',
            ],
        ];
    }
}
