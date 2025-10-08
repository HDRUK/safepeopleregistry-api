<?php

namespace App\Http\Requests\CustodianModelConfig;

use App\Http\Requests\BaseFormRequest;
use App\Models\EntityModelType;
use Illuminate\Validation\Rule;

class GetEntityModelsRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'custodianId' => [
                'required',
                'integer',
                'exists:custodians,id',
            ],
            'entity_model_type' => [
                'required',
                'string',
                Rule::in(EntityModelType::ENTITY_TYPES),
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
        $this->merge(['custodianId' => $this->route('custodianId')]);
    }
}
