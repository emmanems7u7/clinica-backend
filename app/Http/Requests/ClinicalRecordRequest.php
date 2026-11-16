<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClinicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $store = $this->isMethod('POST');

        return [
            'patient_id'      => [$store ? 'required' : 'sometimes', 'integer', Rule::exists('patients', 'id')->whereNull('deleted_at')],
            'dentist_id'      => ['nullable', 'integer', Rule::exists('dentists', 'id')],
            'appointment_id'  => ['nullable', 'integer', 'exists:appointments,id'],
            'diagnosis'       => [$store ? 'required' : 'sometimes', 'string', 'max:10000'],
            'procedure_notes' => ['nullable', 'string', 'max:20000'],
            'prescription'    => ['nullable', 'string', 'max:10000'],
        ];
    }
}
