<?php

namespace App\Http\Requests\Organisations;

use App\Http\Requests\BaseFormRequest;

class StoreUnclaimedOrganisation extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * The Organisation is created already invited, so the lead applicant
     * email is required: they are who the invitation goes to.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'organisation_name' => [
                'required',
                'string',
                'max:255',
            ],
            'lead_applicant_email' => [
                'required',
                'email',
                'max:255',
            ],
        ];
    }
}
