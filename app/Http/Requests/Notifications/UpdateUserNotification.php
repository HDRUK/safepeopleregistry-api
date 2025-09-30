<?php

namespace App\Http\Requests\Notifications;

use App\Http\Requests\BaseFormRequest;

class UpdateUserNotification extends BaseFormRequest
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
                'exists:users,id',
            ],
            'notificationId' => [
                'required',
                'string',
                'exists:notifications,id',
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
        $this->merge(['notificationId' => $this->route('notificationId')]);
    }
}
