<?php

namespace App\Http\Requests\Training;

use App\Http\Requests\BaseFormRequest;

class GetLinkTrainingAndFile extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'trainingId' => [
                'required',
                'integer',
                'exists:trainings,id',
            ],
            'fileId' => [
                'required',
                'integer',
                'exists:files,id',
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
        $this->merge(['trainingId' => $this->route('trainingId')]);
        $this->merge(['fileId' => $this->route('fileId')]);
    }
}
