<?php

namespace App\Http\Requests\Organisations;

use App\Enums\OrganisationAccountStatus;
use App\Enums\OrganisationSroStatus;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Enum;

class GetOrganisations extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_status' => ['sometimes', new Enum(OrganisationAccountStatus::class)],
            'sro_status' => ['sometimes', new Enum(OrganisationSroStatus::class)],
            // Validated so an unrecognised value is rejected rather than ignored by filterWhen.
            'system_approved' => ['sometimes', 'boolean'],
        ];
    }
}
