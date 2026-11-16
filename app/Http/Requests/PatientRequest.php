<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // la autorización se hace con permisos de Spatie en las rutas
    }

    public function rules(): array
    {
        $r = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'first_name'              => [$r, 'string', 'max:100'],
            'last_name'               => [$r, 'string', 'max:100'],
            'document_number'         => [$r, 'string', 'max:30', Rule::unique('patients', 'document_number')->ignore($this->route('id'))],
            'birth_date'              => ['nullable', 'date', 'before:tomorrow'],
            'gender'                  => ['nullable', Rule::in(['male', 'female', 'other'])],
            'phone'                   => ['nullable', 'string', 'max:30'],
            'email'                   => ['nullable', 'email:rfc', 'max:255'],
            'address'                 => ['nullable', 'string', 'max:255'],
            'emergency_contact_name'  => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'allergies'               => ['nullable', 'string', 'max:5000'],
            'medical_history'         => ['nullable', 'string', 'max:10000'],
            'notes'                   => ['nullable', 'string', 'max:5000'],
        ];
    }
}
