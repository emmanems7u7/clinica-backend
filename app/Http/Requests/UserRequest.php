<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $r = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'name'      => [$r, 'string', 'max:150'],
            'email'     => [$r, 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($this->route('id'))],
            'password'  => [$r, Password::min(10)->mixedCase()->numbers()->symbols()],
            'phone'     => ['nullable', 'string', 'max:30'],
            'is_active' => ['sometimes', 'boolean'],
            'roles'     => [$r, 'array', 'min:1'],
            'roles.*'   => ['string', Rule::exists('roles', 'name')],
        ];
    }
}
