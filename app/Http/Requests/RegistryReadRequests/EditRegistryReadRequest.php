<?php

namespace App\Http\Requests\RegistryReadRequests;

use App\Http\Requests\BaseFormRequest;

class EditRegistryReadRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                'exists:registry_read_requests,id',
            ],
            'status' => [
                'integer',
                'required',
                'in:1,2',
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
