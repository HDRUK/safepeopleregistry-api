<?php

namespace App\Http\Requests\Training;

use App\Http\Requests\BaseFormRequest;

class DeleteTraining extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'registry_id' => [
                'integer',
                'required',
                'exists:registries,id',
            ],
        ];
    }
}
