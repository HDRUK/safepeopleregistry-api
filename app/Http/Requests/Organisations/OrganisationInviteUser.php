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
            'id' => [
                'required',
                'integer',
                'exists:organisations,id',
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
