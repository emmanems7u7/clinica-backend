<?php

namespace App\Http\Requests;

use App\Models\Dentist;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class DentistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $store    = $this->isMethod('POST');
        $r        = $store ? 'required' : 'sometimes';
        $dentist  = $store ? null : Dentist::find($this->route('id'));

        return [
            'name'           => [$r, 'string', 'max:150'],
            'email'          => [$r, 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($dentist?->user_id)],
            'password'       => [$r, Password::min(10)->mixedCase()->numbers()->symbols()],
            'phone'          => ['nullable', 'string', 'max:30'],
            'license_number' => [$r, 'string', 'max:50', Rule::unique('dentists', 'license_number')->ignore($this->route('id'))],
            'specialty'      => ['nullable', 'string', 'max:100'],
            'is_active'      => ['sometimes', 'boolean'],
        ];
    }
}
