<?php

namespace App\Http\Requests\Projects;

use App\Http\Requests\BaseFormRequest;

class GetAllUsersFlagProjectByUserId extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'projectId' => [
                'required',
                'integer',
                'exists:projects,id',
            ],
            'userId' => [
                'required',
                'integer',
                'exists:users,id',
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
        $this->merge(['projectId' => $this->route('projectId')]);
        $this->merge(['userId' => $this->route('userId')]);
    }
}
