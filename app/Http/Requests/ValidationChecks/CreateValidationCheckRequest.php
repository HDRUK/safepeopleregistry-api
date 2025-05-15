<?php

namespace App\Http\Requests\ValidationChecks;

use App\Enums\ValidationCheckAppliesTo;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateValidationCheckRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'applies_to' => ['required', new Enum(ValidationCheckAppliesTo::class)],
        ];
    }
}
