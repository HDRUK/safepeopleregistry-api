<?php

namespace App\Http\Requests\Organisations;

use App\Http\Requests\BaseFormRequest;

class OrganisationInviteUser extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // No exists rule: the controllers return 404 for a missing ID. For inviteUser and
            // custodianInviteUser that matches a restricted one, so the response doesn't reveal
            // which restricted IDs exist
            'id' => [
                'required',
                'integer',
            ],
            // The invitee's expected end date, if the Organisation knows it.
            'to' => [
                'nullable',
                'date',
                'after_or_equal:today',
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
