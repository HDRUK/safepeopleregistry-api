<?php

namespace App\Http\Requests\Organisations;

use App\Http\Requests\BaseFormRequest;

class CreateOrganisation extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'organisation_name' => [
                'string',
                'required',
                'max:255',
            ],
            'address_1' => [
                'string',
                'required',
            ],
            'town' => [
                'string',
                'required',
            ],
            'county' => [
                'string',
                'required',
            ],
            'country' => [
                'string',
                'required',
            ],
            'postcode' => [
                'string',
                'required',
            ],
            'companies_house_no' => [
                'string',
                'required',
            ],
            'sector_id' => [
                'integer',
                'required',
                'exists:sectors',
            ],
            'charity_registration_id' => [
                'string',
                'required',
            ],
            'ror_id' => [
                'string',
                'required',
            ],
            'website' => [
                'string',
                'required',
            ],
            'smb_status' => [
                'string',
                'required',
            ],
        ];
    }
}
