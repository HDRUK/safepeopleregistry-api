<?php

namespace App\Http\Requests\Organisations;

use App\Http\Requests\BaseFormRequest;

class UpdateSponsorshipStatus extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'organisation_id' => [
                'required',
                'integer',
                'exists:organisations,id',
            ],
            'project_id' => [
                'required',
                'integer',
                'exists:projects,id',
            ],
            'status' => [
                'required',
                'string',
                'in:approved,rejected',
            ],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(['organisation_id' => $this->route('id')]);
    }
}
