<?php

namespace App\Http\Requests\Affiliations;

use App\Http\Requests\BaseFormRequest;

class UpdateAffiliationEndDate extends BaseFormRequest
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
                'exists:affiliations,id',
            ],
            // Null removes the end date, e.g. when a user withdraws their notice.
            'to' => [
                'present',
                'nullable',
                'date',
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
