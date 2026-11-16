<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $store = $this->isMethod('POST');
        $r     = $store ? 'required' : 'sometimes';

        return [
            'patient_id'   => [$r, 'integer', Rule::exists('patients', 'id')->whereNull('deleted_at')],
            'dentist_id'   => [$r, 'integer', Rule::exists('dentists', 'id')->where('is_active', true)],
            'start_at'     => array_filter([$r, 'date', $store ? 'after_or_equal:now' : null]),
            'end_at'       => [$r, 'date'],
            'reason'       => ['nullable', 'string', 'max:255'],
            'notes'        => ['nullable', 'string', 'max:5000'],
            'treatments'                  => ['sometimes', 'array', 'max:50'],
            'treatments.*.treatment_id'   => ['required', 'integer', Rule::exists('treatments', 'id')->whereNull('deleted_at')],
            'treatments.*.tooth_number'   => ['nullable', 'string', 'max:10'],
            'treatments.*.price'          => ['nullable', 'numeric', 'min:0'],
            'treatments.*.notes'          => ['nullable', 'string', 'max:255'],
        ];
    }
}
