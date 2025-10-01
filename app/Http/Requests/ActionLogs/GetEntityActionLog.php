<?php

namespace App\Http\Requests\ActionLogs;

use App\Models\ActionLog;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;

class GetEntityActionLog extends BaseFormRequest
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
                'exists:action_logs,id',
            ],
            'entity' => [
                'required',
                'string',
                Rule::in(array_keys(ActionLog::ENTITY_MAP)),
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
        $this->merge(['entity' => $this->route('entity')]);
    }
}
