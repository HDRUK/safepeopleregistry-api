<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseFormRequest;

class CheckUserInviteCode extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invitecode' => [
                'required',
                'string',
                'exists:pending_invites,invite_code',
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
        $this->merge(['invitecode' => trim($this->route('inviteCode'))]);
    }
}
