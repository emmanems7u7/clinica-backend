<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $r = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'patient_id'     => [$r, 'integer', Rule::exists('patients', 'id')->whereNull('deleted_at')],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'amount'         => [$r, 'numeric', 'gt:0', 'max:99999999.99'],
            'currency'       => ['sometimes', 'string', 'size:3'],
            'method'         => [$r, Rule::in(['cash', 'card', 'transfer', 'qr'])],
            'status'         => ['sometimes', Rule::in(['pending', 'paid', 'refunded'])],
            'reference'      => ['nullable', 'string', 'max:100'],
            'paid_at'        => ['nullable', 'date'],
            'notes'          => ['nullable', 'string', 'max:255'],
        ];
    }
}
