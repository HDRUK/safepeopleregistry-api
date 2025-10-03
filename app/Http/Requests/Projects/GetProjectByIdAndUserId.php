<?php

namespace App\Http\Requests\Projects;

use App\Http\Requests\BaseFormRequest;

class GetProjectByIdAndUserId extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
