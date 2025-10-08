<?php

namespace App\Http\Requests\VendorWebhookReceivers;

use App\Http\Requests\BaseFormRequest;

class GetVendorWebhookReceiverByProvider extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'provider' => [
                'required',
                'string',
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
        $this->merge(['provider' => $this->route('provider')]);
    }
}
