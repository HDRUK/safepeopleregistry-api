<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseFormRequest;
use App\Models\User;
use Closure;

class UpdateUser extends BaseFormRequest
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
            // Organisation membership is managed via the organisation endpoints, e.g. removeDelegate.
            // Profile forms echo the whole user back, so the current value is accepted.
            'organisation_id' => [$this->unchanged('organisation_id')],
            'is_delegate' => [$this->unchanged('is_delegate')],
        ];
    }

    private function unchanged(string $attribute): Closure
    {
        return function (string $field, mixed $value, Closure $fail) use ($attribute): void {
            $user = User::find($this->route('id'));

            if ($user !== null && $value !== $user->getAttribute($attribute)) {
                $fail("{$attribute} cannot be changed via this endpoint.");
            }
        };
    }

    /**
     * Add Route parameters to the FormRequest.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge(['id' => $this->route('id')]);
    }
}
